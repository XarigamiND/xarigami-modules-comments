<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Searches all active comments based on a set criteria
 *
 * @access private
 * @returns mixed description of return
 */
function comments_userapi_search($args)
{
    if (empty($args) || count($args) < 1) return;

    extract($args);

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];
    $where = '';
    if (!isset($maxitems)) $maxitems = 50;
    // initialize the commentlist array
    $commentlist = array();
    if (!isset($status) || empty($status)) {
        $bindvars = array(_COM_STATUS_ON);
    } else {
       $bindvars = array($status);
    }

    $sql = "SELECT  $ctable[title] AS xar_title,
                    $ctable[cdate] AS xar_date,
                    $ctable[author] AS xar_author,
                    $ctable[cid] AS xar_cid,
                    $ctable[pid] AS xar_pid,
                    $ctable[left] AS xar_left,
                    $ctable[right] AS xar_right,
                    $ctable[postanon] AS xar_postanon,
                    $ctable[modid]  AS xar_modid,
                    $ctable[itemtype]  AS xar_itemtype,
                    $ctable[objectid] AS xar_objectid,
                    $ctable[comment] AS xar_text,
                    $ctable[status] AS xar_status
              FROM  $xartable[comments]
             WHERE  $ctable[status]= ?
               AND  (";

    if (isset($title)) {
        $sql .= "$ctable[title] LIKE ?";
        $bindvars[] = $title;
    }

    if (isset($text)) {
        if (isset($title)) {
            $sql .= " OR ";
        }
        $sql .= "$ctable[comment] LIKE ?";
        $bindvars[] = $text;
    }

    if (isset($author)) {
        if (isset($title) || isset($text)) {
            $sql .= " OR ";
        }
        if ($author == 'anonymous') {
            $sql .= " $ctable[author] = ? OR $ctable[postanon] = ?";
            $bindvars[] = $uid;
            $bindvars[] = 1;
        } else {
            $sql .= " $ctable[author] = ? AND $ctable[postanon] != ?";
            $bindvars[] = $uid;
            $bindvars[] = 1;
        }
    }

    $sql .= ") ORDER BY $ctable[cdate] DESC LIMIT ".$maxitems;

    $result = $dbconn->Execute($sql, $bindvars);
    if (!$result) return;

    // if we have nothing to return
    // we return nothing ;) duh? lol
    if ($result->EOF) return array();

    // zip through the list of results and
    // add it to the array we will return
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $row['xar_uid'] =  $row['xar_author'];
        $row['xar_author'] = xarUserGetVar('name', $row['xar_author']);

        $commentlist[] = $row;
        $result->MoveNext();
    }
    $result->Close();

    if (!xarModLoad('comments', 'renderer')) {
        throw new FunctionNotFoundException('comments_renderer');

    }

    if (!comments_renderer_array_markdepths_bypid($commentlist)) {
        $msg = xarML('Unable to create depth by pid');
        throw new BadParameterException(null,$msg);
    }

    comments_renderer_array_sort($commentlist, _COM_SORTBY_TOPIC, _COM_SORT_ASC);
    // FIXME: excess depth cannot be pruned using this function without knowing
    // the module/itemtype/objectid, which we don't know at this point.
    /*$commentlist = comments_renderer_array_prune_excessdepth(
        array(
            'array_list' => $commentlist,
            'cutoff' => _COM_MAX_DEPTH,
        )
    );*/

    comments_renderer_array_maptree($commentlist);

    return $commentlist;

}

?>