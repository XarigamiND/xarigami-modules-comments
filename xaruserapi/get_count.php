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
 * Get the number of comments for a module item and status
 *
 * @author mikespub
 * @access public
 * @param integer    $modid     the id of the module that these nodes belong to
 * @param integer    $itemtype  the item type that these nodes belong to
 * @param integer    $objectid    the id of the item that these nodes belong to
 * @param integer    $status    the status of the comment: 1 - inactive, 2 - active, 3 - root node*,  32 - locked root node, 4 - marked as spam, 5- mark as notspam (temp status)
 * @returns integer  the number of comments for the particular modid/objectid pair,
 *                   or raise an exception and return false.
 */

include_once('modules/comments/xarincludes/defines.php');
function comments_userapi_get_count($args)
{
    extract($args);

    $exception = false;

    if ( !isset($modid) || empty($modid) ) {
       throw new EmptyParameterException('modid');
    }

    if ( !isset($objectid) || empty($objectid) ) {
        throw new EmptyParameterException('objectid');
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];
    $status = isset($status) ? $status : _COM_STATUS_ALL;

    switch ((int)$status) {
        case _COM_STATUS_ON:
            $where_status = "$ctable[status] = ". _COM_STATUS_ON;
            break;
        case _COM_STATUS_OFF:
            $where_status = "$ctable[status] = ". _COM_STATUS_OFF;
            break;
        case _COM_STATUS_SPAM :
            $where_status = "$ctable[status] = ". _COM_STATUS_SPAM;
            break;
        case _COM_STATUS_ALL :
        default:
            $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE ." AND $ctable[status] != ". _COM_STATUS_NODE_LOCKED ;
    }


    $sql = "SELECT  COUNT($ctable[cid]) as numitems
              FROM  $xartable[comments]
             WHERE  $ctable[objectid]=? AND $ctable[modid]=?
               AND  $where_status ";

// Note: objectid is not an integer here (yet ?)
    $bindvars = array((string) $objectid, (int) $modid);

    if (isset($itemtype) && is_numeric($itemtype)) {
        $sql .= " AND $ctable[itemtype]=?";
        $bindvars[] = (int) $itemtype;
    }

    $result = $dbconn->Execute($sql,$bindvars);
    if (!$result)
        return;

    if ($result->EOF) {
        return 0;
    }

    list($numitems) = $result->fields;

    $result->Close();

    return $numitems;
}

?>
