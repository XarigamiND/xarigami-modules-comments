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
 * Set a notify
 *
 * @param int    $args['user_id'] user_id //xar userid
 * @param id     $args['object_id'] the id of the item
 * @param id     $args['parent_id'] the parent id
 * @param id     $args['itemtype'] the itemtype
 * @param id     $args['module_id'] the module
 * @param int    $args['notifytype'] 1 all //plans for more types 2
 * @return boolean false on failure
 */
function comments_user_setnotify($args)
{
    // Get arguments from argument array
    extract($args);
    if(!xarVarFetch('object_id',  'id',   $object_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('module_id',  'id',   $module_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('user_id',    'id',   $user_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('parent_id',  'id',   $parent_id,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('itemtype',   'id',   $itemtype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('notifytype', 'id',   $notifytype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('returnurl',  'str',  $returnurl,  '', XARVAR_NOT_REQUIRED)) {return;}

    if (empty($user_id)) {
       $userid = xarUserGetVar('uid');
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

    if (!xarSecurityCheck('Comments-Read',0)) return;

    if (empty($notifytype)) {
        $notifytype= 1;
    }

   $setit = xarModAPIFunc('comments','user','setnotify',
      array('modid' => $module_id,'itemtype'=>$itemtype,'objectid'=>$object_id,'parent_id'=>$parent_id,'user_id'=>$user_id,'notifytype'=>$notifytype));


   if (!empty($returnurl)) {
        xarResponseRedirect($returnurl);
   } elseif (!empty($object_id) ) {
        xarResponseRedirect(xarModURL('comments', 'user', 'display', array('modid' => $module_id,'itemtype'=>$itemtype,'objectid'=>$object_id)));
    }
}

?>