<?php
/**
 * Manage comment user notifications
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
function comments_admin_managenotifies($args)
{
   //give an indication to how many notifications per thread and reply
   if (!xarSecurityCheck('Comments-Admin')) return;
   if (!xarVarFetch('deletetype', 'enum:thread:reply:date:orphanthread:orphanreply',  $deletetype,    '',XARVAR_GET_OR_POST)) {return;};
   if (!xarVarFetch('confirm',    'str',  $confirm,    '',XARVAR_GET_OR_POST)) {return;};
   if (!xarVarFetch('comparison', 'int:0:2',  $comparison,   0,XARVAR_GET_OR_POST)) {return;};
   if (!xarVarFetch('date',       'int',  $date,   0,XARVAR_GET_OR_POST)) {return;};
   $data = array();
   $data['menulinks'] = xarModAPIFunc('comments','admin','getmenulinks');
   if ($confirm && xarSecConfirmAuthKey() && !empty($deletetype)) {

        if ($deletetype =='thread') {
            $lockedthreadreplies = xarModAPIFunc('comments','user','countnotifies',
                array('notifytype'=>(int)_COM_NOTIFY_THREAD, 'status'=>_COM_STATUS_NODE_LOCKED, 'countoff'=>TRUE));
            foreach ($lockedthreadreplies as $thread=>$notify) {
               $removenotify = xarModAPIFunc('comments','user','removenotify',array('notifyid'=>$notify['id']));
            }
            $msg = xarML('Thread notifications successfully removed.');
            xarTplSetMessage($msg,'status');

        } elseif ($deletetype =='reply') {
           $allreplies = xarModAPIFunc('comments','user','getnotifies',array('notifytype'=>(int)_COM_NOTIFY_REPLY));

           if (is_array($allreplies)) {
               foreach($allreplies as $reply=>$notify) {
                  //get the root node of the cid
                    $rootinfo = xarModAPIFunc('comments','user','get_node_root',
                                array(  'modid'         => $notify['module_id'],
                                        'itemtype'      => $notify['itemtype'],
                                        'objectid'      => $notify['object_id'],
                                      )
                                    );
                    if ($rootinfo['xar_status'] == _COM_STATUS_NODE_LOCKED ){
                        $removenotify = xarModAPIFunc('comments','user','removenotify',array('notifyid'=>$notify['id']));
                     }
               }
           }
            $msg = xarML('Reply notifications successfully removed.');
            xarTplSetMessage($msg,'status');
        }

   }
   $data['threadnotifies'] = xarModAPIFunc('comments','user','countnotifies',array('notifytype'=> (int)_COM_NOTIFY_THREAD));

   $data['threadnotifieslocked'] =  xarModAPIFunc('comments','user','countnotifies',array('notifytype'=>(int)_COM_NOTIFY_THREAD, 'status'=>_COM_STATUS_NODE_LOCKED));

   $data['replynotifies'] = xarModAPIFunc('comments','user','countnotifies',array('notifytype'=>(int)_COM_NOTIFY_REPLY));
   $allreplies = xarModAPIFunc('comments','user','getnotifies',array('notifytype'=>(int)_COM_NOTIFY_REPLY));
   $replylist = array();
   if (is_array($allreplies)) {
       foreach($allreplies as $reply=>$notify) {
          //get the root node of the cid
            $rootinfo = xarModAPIFunc('comments','user','get_node_root',
                        array(  'modid'         => $notify['module_id'],
                                'itemtype'      => $notify['itemtype'],
                                'objectid'      => $notify['object_id'],
                              )
                            );
            if (isset($rootinfo['xar_status']) && ($rootinfo['xar_status'] == _COM_STATUS_NODE_LOCKED )){
             $replylist[] = $allreplies[$reply];
            }
       }

   }
   $data['comparison'] = 0;
   $data['compareoptions'] = array(1=>'Equal to',2=>'Earlier than');
   $data['replynotifieslocked']= count($replylist);
   $data['authid'] = xarSecGenAuthKey();

   return $data;
}
?>