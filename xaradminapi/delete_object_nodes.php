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
 * Delete all comments attached to the specified objectid / modid pair
 * @access  private
 * @param   integer     $modid      the id of the module that the comments are associated with
 * @param   integer     $modid      the item type that the comments are associated with
 * @param   integer     $objectid   the id of the object within the specified module that the comments are attached to
 * @returns bool true on success, false otherwise
 */
function comments_adminapi_delete_object_nodes( $args )
{
    extract($args);

    if (empty($objectid)) {
        $msg = xarML('Missing or Invalid parameter \'objectid\'!!');
        throw new EmptyParameterException(null,$msg);
    }

    if (empty($modid)) {
        $msg = xarML('Missing or Invalid parameter \'modid\'!!');
        throw new EmptyParameterException(null,$msg);
    }

    if (!isset($itemtype)) {
        $itemtype = 0;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    $sql = "DELETE
              FROM  $xartable[comments]
             WHERE  $ctable[modid]    = $modid
               AND  $ctable[itemtype] = $itemtype
               AND  $ctable[objectid] = '$objectid'";

    $result = $dbconn->Execute($sql);

    if (!isset($result)) {
        return;
    } elseif (!$dbconn->Affected_Rows()) {
        return FALSE;
    } else {
        return TRUE;
    }
}
?>