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
 *  Reconstruct a corrupted celko based table
 *  using the parent id's
 *  @access public
 *  @param  void
 *  @returns boolean  FALSE on error, TRUE on success
 */
function comments_adminapi_celko_reconstruct()
{

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    // initialize the commentlist array
    $commentlist = array();

    // if the depth is zero then we
    // only want one comment
    $sql = "SELECT  $ctable[cid] AS xar_cid,
                    $ctable[pid] AS xar_pid,
                    $ctable[left] AS xar_left,
                    $ctable[right] AS xar_right
              FROM  $xartable[comments]
          ORDER BY  xar_pid DESC";

    $result = $dbconn->Execute($sql);
    if (!$result) return;

    // if we have nothing to return
    // we return nothing ;) duh? lol
    if ($result->EOF) {
        return TRUE;
    }

    // add it to the array we will return
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $tree[$row['xar_cid']] = $row;
        $result->MoveNext();
    }
    $result->Close();

    krsort($tree);

    foreach ($tree as $pid => $node) {
        $newNode = $tree[$node['xar_cid']];

        $tree[$node['xar_pid']]['children'][$node['xar_cid']] = $newNode;
        if ($pid) {
            unset($tree[$node['xar_cid']]);
        }
    }

    krsort($tree);

    // reassign the each node a celko left/right value
    $tree = xarModAPIFunc('comments','admin','celko_assign_slots', $tree);

    // run through each node and update it's entry in the db
    if (!xarModAPIFunc('comments','admin','celko_update', $newtree)) {
        $msg = xarML('Unable to reconstruct the comments table!');
        throw new BadParameterException(null,$msg);
    }

}

?>
