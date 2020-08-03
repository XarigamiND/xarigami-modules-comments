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
 * Delete a node from the tree and reassign it's children to it's parent
 * @access  private
 * @param   integer     $node   the id of the node to delete
 * @param   integer     $pid    the deletion node's parent id (used to reassign the children)
 * @returns bool true on success, false otherwise
 */
function comments_adminapi_delete_node( $args )
{

    extract($args);

    if (empty($node)) {
        $msg = xarML('Missing or Invalid comment id!');
        throw new EmptyParameterException(null,$msg);
    }

    if (empty($pid)) {
        $msg = xarML('Missing or Invalid parent id!');
        throw new EmptyParameterException(null,$msg);
    }

    // Grab the deletion node's left and right values
    $comments = xarModAPIFunc('comments','user','get_one',
                              array('cid' => $node));
    $left = $comments[0]['xar_left'];
    $right = $comments[0]['xar_right'];
    $modid = $comments[0]['xar_modid'];
    $itemtype = $comments[0]['xar_itemtype'];
    $objectid = $comments[0]['xar_objectid'];

    // Call delete hooks for categories, hitcount etc.
    $args['module'] = 'comments';
    $args['itemtype'] = $itemtype;
    $args['itemid'] = $node;
    xarModCallHooks('item', 'delete', $node, $args);

    //Now delete the item ....
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    // delete the node
    $sql = "DELETE
              FROM  $xartable[comments]
             WHERE  xar_cid = ?";
             $bindvars1 = array($node);
    // reset all parent id's == deletion node's id to that of
    // the deletion node's parent id
    $sql2 = "UPDATE $xartable[comments]
                SET xar_pid = ?
              WHERE xar_pid = ?";
              $bindvars2 = array($pid, $node);
    if (!$dbconn->Execute($sql,$bindvars1))
        return;

    if (!$dbconn->Execute($sql2,$bindvars2))
        return;

    // Go through and fix all the l/r values for the comments
    // First we subtract 1 from all the deletion node's children's left and right values
    // and then we subtract 2 from all the nodes > the deletion node's right value
    // and <= the max right value for the table
    if ($right > $left + 1) {
        xarModAPIFunc('comments','user','remove_gap',array('startpoint' => $left,
                                                           'endpoint'   => $right,
                                                           'modid'      => $modid,
                                                           'objectid'   => $objectid,
                                                           'itemtype'   => $itemtype,
                                                           'gapsize'    => 1));
    }
    xarModAPIFunc('comments','user','remove_gap',array('startpoint' => $right,
                                                       'modid'      => $modid,
                                                       'objectid'   => $objectid,
                                                       'itemtype'   => $itemtype,
                                                       'gapsize'    => 2));



    return $dbconn->Affected_Rows();
}
?>
