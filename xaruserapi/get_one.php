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
 * Get a single comment.
 * @access   public
 * @param    integer    $args['cid']       the id of a comment
 * @returns  array   an array containing the sole comment that was requested
                     or an empty array if no comment found
 */
function comments_userapi_get_one( $args )
{
    extract($args);

    //make it useful for common hook calls now this is wrapped by comments_userapi_get
    if (!isset($cid) || empty($cid)) {
        $cid = isset($itemid) ? $itemid :null;
    }
    if(!isset($cid) || empty($cid)) {
        throw new EmptyParameterException('cid');
    }

    if(!isset($status) || !in_array($status,
                                    array(_COM_STATUS_OFF,_COM_STATUS_ON,_COM_STATUS_SPAM,_COM_STATUS_HAM,_COM_STATUS_ROOT_NODE,_COM_STATUS_NODE_LOCKED)
                                    )){
       $status = (int) _COM_STATUS_ALL;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

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
                    $ctable[cid] AS xar_cid,
                    $ctable[pid] AS xar_pid,
                    $ctable[status] AS xar_status,
                    $ctable[left] AS xar_left,
                    $ctable[right] AS xar_right,
                    $ctable[postanon] AS xar_postanon,
                    $ctable[modid] AS xar_modid,
                    $ctable[itemtype] AS xar_itemtype,
                    $ctable[objectid] AS xar_objectid
              FROM  $xartable[comments]
             WHERE  $ctable[cid]=$cid";
    if ($status != _COM_STATUS_ALL) {
       $sql .= " AND  $ctable[status]=$status";
    }
    $result = $dbconn->Execute($sql);
    if(!$result) return;

    // if we have nothing to return
    // we return nothing ;) duh? lol
    if ($result->EOF) {
        return array();
    }

    if (!xarModLoad('comments','renderer')) {
        throw new FunctionNotFoundException('comments_renderer');
    }

    // zip through the list of results and
    // add it to the array we will return
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $row['xar_date'] = $row['xar_datetime'];
        $row['uid'] =  $row['xar_author'];
        if (!empty($row['xar_author']) && $row['xar_author'] > 1){
            $row['xar_author'] = xarUserGetVar('name',$row['xar_author']);
        }
        comments_renderer_wrap_words($row['xar_text'],80);
        $commentlist[] = $row;
        $result->MoveNext();
    }

    $result->Close();

    if (!comments_renderer_array_markdepths_bypid($commentlist)) {
        $msg = xarML('Unable to add depth field to comments!');
        throw new BadParameterException(null,$msg); //jojo - move away at least nearer tofunction raising issue
    }

    return $commentlist;
}

?>
