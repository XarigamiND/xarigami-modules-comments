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
 * get the list of modules and itemtypes for the items that we're commenting on
 *
 * @param   string  status optional status to count: ALL (minus root nodes), ACTIVE, INACTIVE, SPAM
 * @param integer modid optional module id you want to count for
 * @param integer itemtype optional item type you want to count for
 * @returns array
 * @return $array[$modid][$itemtype] = array('items' => $numitems,'comments' => $numcomments);
 */
function comments_userapi_getmodules($args)
{
    // Get arguments from argument array
    extract($args);

    // Security check
    if (!xarSecurityCheck('Comments-Read')) return;

    if (empty($status)) {
        $status = 'all';
    }
    $status = strtolower($status);

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
            $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE;
    }
    if (!empty($modid)) {
        $where_mod = " AND $ctable[modid] = $modid";
        if (isset($itemtype)) {
            $where_mod .= " AND $ctable[itemtype] = $itemtype";
        }
    } else {
        $where_mod = '';
    }

    // Get items
    if($dbconn->databaseType == 'sqlite') {
        //Older sqlite versions don't allow COUNT(DISTINCT ..) and related subqueries
        $sql = "SELECT $ctable[modid], $ctable[itemtype], COUNT(*), 0 ";
    } else {
        $sql = "SELECT $ctable[modid], $ctable[itemtype], COUNT(*),
                       COUNT(DISTINCT $ctable[objectid])";
    }
    $sql .= "FROM $commentstable
             WHERE $where_status $where_mod
             GROUP BY $ctable[modid], $ctable[itemtype]";

    $result = $dbconn->Execute($sql);
    if (!$result) return;

    $modlist = array();
    while (!$result->EOF) {
        list($modid,$itemtype,$numcomments,$numitems) = $result->fields;
        if (!isset($modlist[$modid])) {
            $modlist[$modid] = array();
        }
        if ($dbconn->databaseType == 'sqlite') {
            $sql = "SELECT COUNT(*)
                    FROM (SELECT DISTINCT $ctable[objectid]
                          FROM $commentstable
                          WHERE $where_status AND
                                $ctable[modid] = $modid AND
                                $ctable[itemtype] = $itemtype
                    )";
            $subresult = $dbconn->Execute($sql);
            list($numitems) = $subresult->fields;
        }
        $modlist[$modid][$itemtype] = array('items' => $numitems, 'comments' => $numcomments);
        $result->MoveNext();
    }
    $result->close();

    return $modlist;
}

?>
