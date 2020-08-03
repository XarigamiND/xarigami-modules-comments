<?php
/**
 * Comments - set a notify
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
 * @return boolean false on failure
 */
function comments_userapi_setnotify($args)
{
    extract($args);
    if(!xarVarFetch('object_id',  'id',   $object_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('module_id',  'id',   $module_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('user_id',    'id',   $user_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('parent_id',  'id',   $parent_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('itemtype',   'id',   $itemtype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('notifytype', 'id',   $notifytype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('returnurl',  'str',  $returnurl,  '', XARVAR_NOT_REQUIRED)) {return;}

    if (empty($user_id)) {
       $user_id = xarUserGetVar('uid');
    }
    if (!xarUserIsLoggedIn()) {
     $msg = xarML('You must be logged in to receive notifications.');
        return xarResponseForbidden($msg);
    }

    // unless this is an item subscription (new comments for a module-itemtype-object - type 1), we need an parentid too
    if ((empty($module_id)) && (empty($itemtype) && empty($object_id) && ($notifytype !=1))) {
        $msg = xarML('You must have an parent comment to subscribe, or a itemtype.');
        throw new BadParameterException(null,$msg);
    }

     if (!isset($parent_id)) {
      $parent_id = 0;
    }

   if (!isset($itemtype)) {
      $itemtype = 0;
    }
    if (!xarSecurityCheck('Comments-Read',0)) return;

    if (empty($notifytype)) {
        $notifytype= 1;
    }
    if (!isset($extra)) {
        $extra= '';
    }

    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $notifyTable = $xartable['comments_notify'];

    // Get next ID in table
    if (empty($id) || !is_numeric($id) || $id == 0) {
        $nextId = $dbconn->GenId($notifyTable);
    } else {
        $nextId = $id;
    }

    // Add item
    $query = "INSERT INTO $notifyTable (
              xar_id,
              xar_user_id,
              xar_parent_id,
              xar_module_id,
              xar_itemtype,
              xar_object_id,
              xar_notifytype,
              xar_extra)
            VALUES (?,?,?,?,?,?,?,?)";
    $bindvars = array($nextId,
                      (int)  $user_id,
                      (int)  $parent_id,
                      (int)  $module_id,
                      (int)  $itemtype,
                      (int)  $object_id,
                      (int)  $notifytype,
                      (string) $extra);
    $result = $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    if (empty($id) || !is_numeric($id) || $id == 0) {
        $id = $dbconn->PO_Insert_ID($notifyTable, 'xar_id');
    }

    return $id;
}

?>