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
 * The user menu that is used in roles/account
 */
function comments_user_usermenu($args)
{
    extract($args);
    $settings = xarModAPIFunc('comments','user','getoptions',array('modid'=>xarModGetIDFromName('roles')));
    // Security Check
    if (xarSecurityCheck('Comments-Read',0) &&
       (xarModGetVar('comments','usersetrendering') || $settings['showoptions'])) {

        if(!xarVarFetch('phase','str', $phase, 'menu', XARVAR_NOT_REQUIRED)) {return;}

        xarTplSetPageTitle(xarModGetVar('themes', 'SiteName').' :: '.
                           xarVarPrepForDisplay(xarML('Comments'))
                           .' :: '.xarVarPrepForDisplay(xarML('Your Account Preferences')));

        switch(strtolower($phase)) {
        case 'menu':

            $icon = xarTplGetImage('comments.gif', 'comments');
            $data = xarTplModule('comments','user', 'usermenu_icon',
                array('icon' => $icon,
                      'usermenu_form_url' => xarModURL('comments', 'user', 'usermenu', array('phase' => 'form'))
                     ));
            break;

        case 'form':


            $settings['max_depth'] = _COM_MAX_DEPTH - 1;
            $currentuser = xarUserGetVar('uid');
            $notifylist = '';
            if ($settings['usecommentnotify']) {
                $notifies = xarModAPIFunc('comments','user','getnotifies',array('user_id'=>$currentuser));//project notifies

                if (!is_array($notifies) || count($notifies) ==0) {
                    $notifies = '';
                    $notifylist = '';
                } else {
                    $notifylist = array();

                    foreach ($notifies as $k=>$cnotify) {
                         if ($cnotify['notifytype']==1) {
                             $module = xarModGetNameFromId($cnotify['module_id']);

                            $itemdata = xarModAPIFunc($module,'user','get',array('itemid'=>$cnotify['object_id']));

                            if (is_array($itemdata) && isset($itemdata['title'])) { //try
                                $title =$itemdata['title'];
                            } else {
                                $title = xarML('Thread in #(1)',$module);
                            }
                        } elseif ($cnotify['notifytype']==2){
                           $itemdata = xarModAPIFunc('comments','user','get_one',array('cid'=>$cnotify['parent_id']));
                           $itemdata=current($itemdata);
                           $title = $itemdata['xar_title'];
                        }

                            $remove = xarModURL('comments','user','removenotify',
                            array('parent_id' =>$cnotify['parent_id'],
                                  'module_id' =>$cnotify['module_id'],
                                  'itemtype' =>$cnotify['itemtype'],
                                  'object_id' =>$cnotify['object_id'],
                                  'user_id'=>$currentuser,
                                  'notifytype' => $cnotify['notifytype'],
                                  'returnurl'=>xarServerGetCurrentURL()));

                             $notifylist[] =array('parent_id' =>$cnotify['parent_id'],
                                                  'module_id' =>$cnotify['module_id'],
                                                  'itemtype' =>$cnotify['itemtype'],
                                                  'object_id' =>$cnotify['object_id'],
                                                  'user_id'=>$currentuser,
                                                  'notifytype' => $cnotify['notifytype'],
                                                  'remove'=>$remove,
                                                  'title'=>$title);
                    }
                    foreach ($notifylist as $key => $row) {
                        $objecttype[$key]  = $row['object_id'];
                        $notifytype[$key] = $row['notifytype'];
                    }
                    if (is_array($notifylist) && count($notifylist)>0) {
                        array_multisort($notifytype,SORT_ASC,$objecttype,SORT_ASC,$notifylist);
                    } else {
                       $notifylist = '';
                    }
                 }
            }

            $authid = xarSecGenAuthKey('comments');
            $data = xarTplModule('comments','user', 'usermenu_form', array('authid'   => $authid,
                                                                           'settings' => $settings,
                                                                           'notifylist'=> $notifylist));
            break;

        case 'update':

            if(!xarVarFetch('settings','array', $settings, array(), XARVAR_NOT_REQUIRED)) {return;}

            if (count($settings) <= 0) {
                throw new EmptyParameterException('settings');
            }

            // Confirm authorisation code.
            if (!xarSecConfirmAuthKey())
                return;

            xarModAPIFunc('comments','user','setoptions',$settings);

            // Redirect
            xarResponseRedirect(xarModURL('roles', 'user', 'account',
                                          array('moduleload' => 'comments')));

            break;
        }

    } else {
       $data=''; //make sure hooks in usermenu don't fail because this function returns unset
    }
        return $data;
}

?>