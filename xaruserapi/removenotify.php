<?php
/**
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
  *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Remove a notify
 *
 * @param int    $args['user_id'] user_id //xar userid
 * @param id     $args['object_id'] the id of the item
 * @param id     $args['parent_id'] the parent id
 * @param id     $args['itemtype'] the itemtype
 * @param id     $args['module_id'] the module
 * @param int    $args['notifytype'] 1 all //plans for more types 2
 * @param id     $args['notifyid'] the id of the notify
 * @return boolean false on failure
 */
function comments_userapi_removenotify($args)
{
    extract($args);
    if(!xarVarFetch('object_id',  'id',   $object_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('module_id',  'id',   $module_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('user_id',    'id',   $user_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('parent_id',  'id',   $parent_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('itemtype',   'id',   $itemtype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('notifytype', 'id',   $notifytype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('returnurl',  'str',  $returnurl,  '', XARVAR_NOT_REQUIRED)) {return;}

    if (!isset($notifyid)) {
        if (empty($user_id)) {
           $user_id = xarUserGetVar('uid');
        }
        if (!xarUserIsLoggedIn()) {
         $msg = xarML('You must be logged in to receive notifications.');
            return xarResponseForbidden($msg);
        }
    } else {
        //must be admin to do anything here
        if (!xarSecurityCheck('Comments-Admin')) return;
    }

    // unless this is an item subscription (new comments for a module-itemtype-object - type 1), we need an parentid too
    if (empty($module_id) && empty($itemtype) && empty($object_id) && ($notifytype !=1) && !isset($notifyid)) {
        $msg = xarML('You must have a parent comment to subscribe, or a itemtype.');
               throw new BadParameterException(null,$msg);

    }

     if (!isset($parent_id)) {
      $parent_id = 0;
    }

    if (!xarSecurityCheck('Comments-Read',0)) return;

    if (empty($notifytype)) {
        $notifytype= 1;
    }


    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $notifyTable = $xartable['comments_notify'];

    // Delete item
    $query = "DELETE FROM $notifyTable";

    if (!isset($notifyid)) {
        $query .= " WHERE xar_user_id = ? and xar_module_id = ? and xar_parent_id = ? and xar_itemtype=? and xar_object_id = ? and xar_notifytype = ?";
        $bindvars = array($user_id,$module_id,$parent_id,$itemtype,$object_id,$notifytype);
        $result = $dbconn->Execute($query,$bindvars);
    } else {
        $query .= " WHERE xar_id = ? ";
        $bindvars = array($notifyid);
        $result = $dbconn->Execute($query,$bindvars);
    }
    if (!$result) return;

    return true;
}

?>
