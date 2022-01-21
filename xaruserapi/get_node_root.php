<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Grab the id, left and right values for the
 * root node of a particular comment.
 *
 * @access   public
 * @param    integer     modid      The module that comment is attached to
 * @param    integer     objectid   The particular object within that module
 * @param    integer     itemtype   The itemtype of that object
 * @returns  array an array containing the cid (id), left and right values and status, or an
 *                 empty array if the comment_id specified doesn't exist
 */
function comments_userapi_get_node_root( $args )
{

    extract ($args);

    $exception = false;

    if (!isset($modid) || empty($modid)) {
        throw new EmptyParameterException('modid');
    }

    if (!isset($objectid) || empty($objectid)) {
        throw new EmptyParameterException('objectid');
    }

    if (empty($itemtype)) {
        $itemtype = 0;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];

    //jojo - going to grab the status now and pass it back
    //So far all root nodes are displayed and has only been one status
    //and so this function always returns the root node if it exists
    //let's not select on _COM_STATUS_ROOT_NODE - no use, and more useful not to
    //as we will introduce other root node status

    // grab the root node's id, left and right values and status
    // based on the objectid/modid pair
    $sql = "SELECT  $ctable[cid], $ctable[left], $ctable[right], $ctable[status]
              FROM  $xartable[comments]
             WHERE  $ctable[modid]=?
               AND  $ctable[itemtype]=?
               AND  $ctable[objectid]=?
               AND  $ctable[pid]=0 ";
    // objectid is still a string for now
    $bindvars = array((int) $modid, (int) $itemtype, (string) $objectid); //, (int) _COM_STATUS_ROOT_NODE);

    $result = $dbconn->Execute($sql,$bindvars);

    if(!$result)
        return;

    $count=$result->RecordCount();

    assert($count==1 || $count==0);

    if (!$result->EOF) {
        $node = $result->GetRowAssoc(false);
    } else {
        $node = array();
    }
    $result->Close();

    return $node;
}

?>
