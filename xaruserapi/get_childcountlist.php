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
 * Get the number of approved children comments for a list of comment ids
 *
 * @author mikespub
 * @access public
 * @param integer  $left the left limit for the list of comment ids
 * @param integer  $right the right limit for the list of comment ids
 * @param integer  $modid/$itemtype/$objectid of the module selected
 * @returns array  the number of child comments for each comment id,
 *                   or raise an exception and return false.
 */
function comments_userapi_get_childcountlist($args)
{

    extract($args);
    if (!isset($left) || !is_numeric($left) || !isset($right) || !is_numeric($right)) {
        $msg = xarML('Invalid #(1)', 'left/right');
        throw new BadParameterException(null,$msg);
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];

    $bind = array((int)$left, (int)$right, _COM_STATUS_ON, (int)$modid, (int)$objectid, (int)$itemtype);

    $sql = "SELECT P1.xar_cid, COUNT(P2.xar_cid) AS numitems"
        . " FROM $xartable[comments] AS P1, $xartable[comments] AS P2"
        . " WHERE P1.xar_modid = P2.xar_modid AND P1.xar_itemtype = P2.xar_itemtype AND P1.xar_objectid = P2.xar_objectid"
        . " AND P2.xar_left >= P1.xar_left AND P2.xar_left <= P1.xar_right"
        . " AND P1.xar_left >= ? AND P1.xar_right <= ?"
        . " AND P2.xar_status = ?"
        . " AND P1.xar_modid = ? AND P1.xar_objectid = ? AND P1.xar_itemtype = ?"
        . " GROUP BY P1.xar_cid";

    $result = $dbconn->Execute($sql, $bind);
    if (!$result) return;

    if ($result->EOF) return array();

    $count = array();
    while (!$result->EOF) {
        list($id, $numitems) = $result->fields;
        // return total count - 1 ... the -1 is so we don't count the comment root.
        $count[$id] = $numitems - 1;
        $result->MoveNext();
    }
    $result->Close();

    return $count;
}

?>