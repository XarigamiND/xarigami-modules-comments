<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Get a single comment or a list of comments. Depending on the parameters passed
 * you can retrieve either a single comment, a complete list of comments, a complete
 * list of comments down to a certain depth or, lastly, a specific branch of comments
 * starting from a specified root node and traversing the complete branch
 *
 * if you leave out the objectid, you -must- at least specify the author id
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access public
 * @param integer    $modid     the id of the module that these nodes belong to
 * @param integer    $itemtype  the item type that these nodes belong to
 * @param integer    $objectid  (optional) the id of the item that these nodes belong to
 * @param integer    $cid       (optional) the id of a comment
 * @param integer    $status    (optional) only pull comments with this status
 * @param integer    $author    (optional) only pull comments by this author
 * @param boolean    $reverse   (optional) reverse sort order from the database
 * @param str       $order  (optional) order field name
 * @param str       $sort (optional) sort asc or desc
 * @returns array     an array of comments or an empty array if no comments
 *                   found for the particular modid/objectid pair, or raise an
 *                   exception and return false.
 */
function comments_userapi_get_multiple($args)
{
    extract($args);

    if ( !isset($modid) || empty($modid) ) {
        throw new EmptyParameterException('modid');
    }

    if ( (!isset($objectid) || empty($objectid)) && !isset($author) ) {
      throw new EmptyParameterException('objectid');
    } else
        if (!isset($objectid) && isset($author)) {
            $objectid = 0;
    }

    if (!isset($cid) || !is_numeric($cid)) {
        $cid = 0;
    } else {
        $nodelr = xarModAPIFunc('comments', 'user', 'get_node_lrvalues',
                                 array('cid' => $cid));
    }
    if (!isset($sort)) $sort = '';
    if (!isset($order)) $order = '';


    // Optional argument for Pager -
    // for those modules that use comments and require this
     if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }
    if (!isset($status)) {
        $status = _COM_STATUS_ON;
    }
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    switch ((int)$status) {
        case  _COM_STATUS_ON:
            $where_status = "$ctable[status] = ". _COM_STATUS_ON;
            break;
        case  _COM_STATUS_OFF:
            $where_status = "$ctable[status] = ". _COM_STATUS_OFF;
            break;
        case _COM_STATUS_SPAM :
            $where_status = "$ctable[status] = ". _COM_STATUS_SPAM;
            break;
        case _COM_STATUS_ALL :
        default:
            $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE ." AND $ctable[status] != ". _COM_STATUS_NODE_LOCKED ;
    }
    // initialize the commentlist array
    $commentlist = array();

    // if the depth is zero then we
    // only want one comment
    $sql = "SELECT  $ctable[title] AS xar_title,
                    $ctable[cdate] AS xar_datetime,
                    $ctable[hostname] AS xar_hostname,
                    $ctable[comment] AS xar_text,
                    $ctable[author] AS xar_author,
                    $ctable[author] AS xar_uid,
                    $ctable[objectid] AS xar_objectid,
                    $ctable[cid] AS xar_cid,
                    $ctable[pid] AS xar_pid,
                    $ctable[status] AS xar_status,
                    $ctable[left] AS xar_left,
                    $ctable[right] AS xar_right,
                    $ctable[postanon] AS xar_postanon
              FROM  $xartable[comments]
              WHERE $where_status
             AND  $ctable[modid]=? ";

    $bindvars = array();
    $bindvars[] = (int) $modid;

    if (isset($itemtype) && is_numeric($itemtype)) {
        $sql .= " AND $ctable[itemtype]=?";
        $bindvars[] = (int) $itemtype;
    }

    if (isset($objectid) && !empty($objectid)) {
        $sql .= " AND $ctable[objectid]=?";
        $bindvars[] = (string) $objectid; // yes, this is a string in the table
    }

    if (isset($author) && $author > 0) {
        $sql .= " AND $ctable[author] = ?";
        $bindvars[] = (int) $author;
    }

    if ($cid > 0) {
        $sql .= " AND ($ctable[left] >= ?";
        $sql .= " AND  $ctable[right] <= ?)";
        $bindvars[] = (int) $nodelr['xar_left'];
        $bindvars[] = (int) $nodelr['xar_right'];
    }

    if (!empty($reverse)) {
        $sql .= " ORDER BY $ctable[right] DESC";
    } elseif (!empty($order)) {
        $sql .= " ORDER BY $ctable[$order] $sort";
    } else {
        $sql .= " ORDER BY $ctable[left]";
    }


// cfr. xarcachemanager - this approach might change later
    $expire = xarModGetVar('comments','cache.userapi.get_multiple');

    //Add select limit for modules that call this function and need Pager
    if (isset($numitems) && is_numeric($numitems)) {
        if (!empty($expire)){
            $result = $dbconn->CacheSelectLimit($expire, $sql, $numitems, $startnum-1,$bindvars);
        } else {
            $result = $dbconn->SelectLimit($sql, $numitems, $startnum-1,$bindvars);
        }
    } else {
        if (!empty($expire)){
            $result = $dbconn->CacheExecute($expire,$query,$bindvars);
        } else {
            $result = $dbconn->Execute($query,$bindvars);
        }
    }
    if (!$result) return;

    // if we have nothing to return
    if ($result->EOF) {
        return array();
    }

    if (!xarModLoad('comments','renderer')) {
        $msg = xarML('Unable to load #(1) #(2)','comments','renderer');
        throw new FunctionNotFoundException('comments_renderer');
    }

    // zip through the list of results and
    // add it to the array we will return
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $row['xar_date'] = $row['xar_datetime'];
        $row['xar_uid'] = $row['xar_author'];
        $row['xar_author'] = xarUserGetVar('name',$row['xar_author']);
        comments_renderer_wrap_words($row['xar_text'],80);
        $commentlist[] = $row;
        $result->MoveNext();
    }
    $result->Close();

    if (!comments_renderer_array_markdepths_bypid($commentlist)) {
        $msg = xarML('Unable to create depth by pid');
        throw new BadParameterException(null,$msg);
    }

    return $commentlist;
}

?>