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
 * get number of comments for all items or a list of items
 *
 * @param $args['modname'] name of the module you want items from, or
 * @param $args['modid'] module id you want items from
 * @param $args['itemtype'] item type (optional)
 * @param $args['itemids'] array of item IDs
 * @param $args['status'] optional status to count: ALL (minus root nodes), ACTIVE, INACTIVE, Marked as SPAM
 * @param $args['numitems'] optional number of items to return
 * @param $args['startnum'] optional start at this number (1-based)
 * @returns array
 * @return $array[$itemid] = $numcomments;
 */
function comments_userapi_getitems($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($modname) && !isset($modid)) {
        throw new EmptyParameterException('modid or modname');
    }
    if (!empty($modname)) {
        $modid = xarModGetIDFromName($modname);
    }
    if (empty($modid)) {
        throw new EmptyParameterException('modid');
    }

    if (!isset($itemtype)) {
        $itemtype = 0;
    }

    if (empty($status)) {
        $status = 'all';
    }
    $status = strtolower($status);

    // Security check
    if (!isset($mask)){
        $mask = 'Comments-Read';
    }
    if (!xarSecurityCheck($mask)) return;

    // Database information
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $commentstable = $xartable['comments'];
    $ctable = $xartable['comments_column'];

    switch ($status) {
        case 'active':
            $where_status = "$ctable[status] = ". _COM_STATUS_ON;
            break;
        case 'inactive':
            $where_status = "$ctable[status] = ". _COM_STATUS_OFF;
            break;
        case 'spam' :
            $where_status = "$ctable[status] = ". _COM_STATUS_SPAM;
            break;
        default:
        case 'all':
            $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE ." AND $ctable[status] != ". _COM_STATUS_NODE_LOCKED ;
    }
    // Get items
    $bindvars = array();
    $query = "SELECT $ctable[objectid], COUNT(*)
                FROM $commentstable
               WHERE $ctable[modid] = ?
                 AND $ctable[itemtype] = ?
                 AND $where_status " ;
    $bindvars[] = (int) $modid; $bindvars[] = (int) $itemtype;
    if (isset($itemids) && count($itemids) > 0) {
        $bindmarkers = '?' . str_repeat(',?', count($itemids)-1);
        $bindvars = array_merge($bindvars, $itemids);
        $query .= " AND $ctable[objectid] IN ($bindmarkers)";
    }
    $query .= " GROUP BY $ctable[objectid]
                ORDER BY $ctable[objectid]";
//                ORDER BY (1 + $ctable[objectid]";
//
// CHECKME: dirty trick to try & force integer ordering (CAST and CONVERT are for MySQL 4.0.2 and higher
// <rabbitt> commented that line out because it won't work with PostgreSQL - not sure about others.

    if (!empty($numitems)) {
        if (empty($startnum)) {
            $startnum = 1;
        }
        $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1,$bindvars);
    } else {
        $result = $dbconn->Execute($query,$bindvars);
    }
    if (!$result) return;

    $getitems = array();
    while (!$result->EOF) {
        list($id,$numcomments) = $result->fields;
        $getitems[$id] = $numcomments;
        $result->MoveNext();
    }
    $result->close();
    return $getitems;
}
?>
