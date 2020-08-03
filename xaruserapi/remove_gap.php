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
 * Remove a gap in the celko tree
 * @access   private
 * @param    integer    $startpoint    starting point for removing gap
 * @param    integer    $endpoint      end point for removing gap
 * @param    integer    $gapsize       the size of the gap to remove
 * @param    integer    $modid         the module id
 * @param    integer    $itemtype      the item type
 * @param    string     $objectid      the item id
 * @returns  integer    number of affected rows or false [0] on error
 */
function comments_userapi_remove_gap( $args )
{

    extract($args);

    if (!isset($startpoint)) {
        throw new EmptyParameterException('startpoint');

    }

    // 1 is used when a node is deleted and children are attached to the parent
    if (!isset($gapsize) || $gapsize < 1) {
        $gapsize = 2;
    }

    if (!isset($endpoint) || !is_numeric($endpoint)) {
        $endpoint = NULL;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $sql_left  = "UPDATE $xartable[comments]
                     SET xar_left = (xar_left - $gapsize)
                   WHERE xar_left > $startpoint";

    $sql_right = "UPDATE $xartable[comments]
                     SET xar_right = (xar_right - $gapsize)
                   WHERE xar_right >= $startpoint";

    // if we have an endpoint, use it :)
    if (!empty($endpoint) && $endpoint !== NULL) {
        $sql_left   .= " AND xar_left <= $endpoint";
        $sql_right  .= " AND xar_right <= $endpoint";
    }
    // if we have a modid, use it
    if (!empty($modid)) {
        $sql_left   .= " AND xar_modid = $modid";
        $sql_right  .= " AND xar_modid = $modid";
    }
    // if we have an itemtype, use it (0 is acceptable too here)
    if (isset($itemtype)) {
        $sql_left   .= " AND xar_itemtype = $itemtype";
        $sql_right  .= " AND xar_itemtype = $itemtype";
    }
    // if we have an objectid, use it
    if (!empty($objectid)) {
        $sql_left   .= " AND xar_objectid = '$objectid'";
        $sql_right  .= " AND xar_objectid = '$objectid'";
    }

    $result1 = $dbconn->Execute($sql_left);
    $result2 = $dbconn->Execute($sql_right);

    if(!$result1 || !$result2) {
        return;
    }

    return $dbconn->Affected_Rows();
}

?>
