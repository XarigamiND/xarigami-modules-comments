<?php
/**
 * Comments do notifications
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Do notify
 *
 * @param int    $args['user_id'] user_id //xar userid
 * @param id     $args['object_id'] the id of the item
 * @param id     $args['parent_id'] the parent id
 * @param id     $args['itemtype'] the itemtype
 * @param id     $args['module_id'] the module
 * @param int    $args['notifytype'] 1 all //plans for more types 2
 * #param int    $args['author']
 * @return boolean false on failure
 */
function comments_userapi_donotify($args)
{
    extract($args);

    if (!isset($object_id) || empty($object_id)) {
         xarLogMessage("Comments: notification mail routine - no objectid");
        return; //just continue on through the creation process
    }
        xarLogMessage("Comments: notification mail routine - entered donotify");
    if (!isset($module_id)) {
       return;
          xarLogMessage("Comments: notification mail routine - no module_id");
    }

    if (!isset($user_id)){
       $user_id = 0;
    }

   if (!isset($notifytype)) {
        $notifytype =1;
    }
     //set default just in case
    $tomail = xarModGetvar('comments','notifyfromemail'); //use our default notify, dummy
    $toname =  xarModGetVar('themes','siteName').xarML(' Discussion Subscribers');
    $fromname =  xarModGetVar('comments','notifyfromname');
    $fromemail = xarModGetvar('comments','notifyfromemail');
    $sitename = xarModGetVar('themes','SiteName');
    $siteurl = xarServerGetBaseURL();

    $today = getdate();
    $month = $today['month'];
    $mday  = $today['mday'];
    $year  = $today['year'];
    $todaydate = $mday.' '.$month.', '.$year;

    //we do not know here if it is just new or a reply at this time
    //first get all the plain notifies for this object
    $notifylist = xarModAPIFunc('comments','user','getnotifies',array('parent_id'=>$parent_id,'module_id'=>$module_id,'itemtype'=>$itemtype,'object_id'=>$object_id, 'notifytype'=>$notifytype));
    if (!is_array($notifylist)|| empty($notifylist)) {
       //there are no notifies
       xarLogMessage("Comments: notification mail routine - no notifies to process");
        return; //there is nothing to do
    }
    //if a user creates a comment - do not notify them
    foreach ($notifylist as $k=>$v) {
        if ($v['user_id'] == $author) {
            unset($notifylist[$k]);
        }
    }
   //we do not want double notifies
   //if there is a reply and the person is also notified for new comments on an object - only do the reply notification
    if (($notifytype ==1) && ($parent_id>0)) { //thread notify - do not do this if the user has a reply notify
        //get the reply list
        $notifyreplies = xarModAPIFunc('comments','user','getnotifies',array('parent_id'=>$parent_id,'module_id'=>$module_id,'itemtype'=>$itemtype,'object_id'=>$object_id, 'notifytype'=>2));
        foreach ($notifyreplies as $k=>$v) {
            foreach ($notifylist as $n=>$l) {
                if ($v['user_id'] == $l['user_id']) {
                    unset($notifylist[$n]);
                }
            }
        }
    }
    if ((count($notifylist)<=0) || empty($notifylist)) {
       //there are no notifies
       xarLogMessage("Comments: notification mail routine - no notifies to process");
        return; //there is nothing to do

    }

    //we have some notifications to do
    $bccrecipients = array();
    foreach ($notifylist as $key=>$notifydata) {
       $useremail = xarUserGetVar('email',$notifydata['user_id']);
       $bccrecipients[$useremail]='';
    }

    $module = xarModGetNameFromID($module_id);
    //get some object info

     if (!isset($title)) $title = '';
     $itemlinks = '';
     if (xarModIsHooked('comments',$module,$itemtype)) {
        $itemlinks = xarModAPIFunc($module,'user','getitemlinks',array('itemtype'=>$itemtype,'itemids'=>array($object_id)),0);
        $itemlink = $itemlinks[$object_id];
        $threadurl = html_entity_decode($itemlink['url']);
        $title = $itemlink['label'];
        $commenturl = xarModURL('comments','user','display',array('modid'=>$module_id,'itemtype'=>$itemtype,'objectid'=>$object_id,'cid'=>$cid),false);
     }

     if (!xarModIsHooked($module,'comments',$itemtype) && (!is_array($itemlinks))) {
        $commentinfo = xarModAPIFunc('comments','user','get_one',array('cid'=>$cid));
        $commentinfo = current($commentinfo);
        $commenturl = xarModURL('comments','user','display',array('modid'=>$module_id,'itemtype'=>$itemtype,'objectid'=>$object_id,'cid'=>$cid),false);
        $threadurl = xarModURL('comments','user','display',array('pid'=>$parent_id,'modid'=>$module_id,'itemtype'=>$itemtype,'objectid'=>$object_id),false);
        $title = $commentinfo['xar_title'];
     }
    switch ($notifytype) {
        case '2': //notification of reply in a thread
            $subject = xarML('Comment reply at #(1)',$sitename);
          if (!empty($title)) {
               $textmessage = xarML('A reply was made in "#(1)" on "#(2)"',$module,$title);
           } else {
               $textmessage = xarML('A reply was made in "#(1): at "#(2)"', $module, $sitename);
           }
            break;
        case '1':
        default:

            $subject = xarML('Notification of a new comment at #(1)', $sitename);
           //notificaton of a comment on an object/itemtype/modid combo
           if (!empty($title)) {
               $textmessage = xarML('New comment has been made in "#(1)" at "#(2)"',$title,$sitename);
           } else {
               $textmessage = xarML('A new comment has been made in "#(1)" at "#(2)")', $module, $sitename);
           }
        break;
    }

    $htmlmessage = html_entity_decode(xarVarPrepHTMLDisplay($textmessage)); //php >=4.3

    if (!empty($template)){
             $htmltemplate = 'html_' . $template;
             $texttemplate = 'text_' . $template;
        } else {
             $htmltemplate =  'html';
             $texttemplate =  'text';
        }
    //prepare the emails
       /* now let's do the html message to admin */
     xarLogMessage("Contrails: preparing emails to send");
    $htmlarray=    array( 'subject'     => $subject,
                          'message'     => $htmlmessage,
                          'commenturl'  => $commenturl,
                          'threadurl'   => $threadurl,
                          'sitename'    => $sitename,
                          'siteurl'     => $siteurl,
                          'todaydate'   => $todaydate);

    $htmlmessage= xarTplModule('comments','user','notifymail',$htmlarray,$htmltemplate);
    /* We don't need this - should automatically fall back to the html template
    if (xarCurrentErrorID() == 'TEMPLATE_NOT_EXIST') {
        xarErrorHandled();
        $htmlmessage= xarTplModule('comments', 'user', 'notifymail',$htmlarray,'html');
    }
    */
    $textarray =  array(     'subject'     => $subject,
                             'message' => $textmessage,
                             'commenturl'    => $commenturl,
                             'threadurl'   =>$threadurl,
                             'sitename'    => $sitename,
                             'siteurl'     => $siteurl,
                             'todaydate'   => $todaydate);

    /* Let's do text message */
    $textmessage= xarTplModule('comments','user','notifymail',$textarray,$texttemplate);
    /*
    if (xarCurrentErrorID() == 'TEMPLATE_NOT_EXIST') {
        xarErrorHandled();
        $textmessage= xarTplModule('comments', 'user', 'notifymail',$textarray,'text');
    }*/

    /* send email to everyone on the list */
    $args = array('info'         => $tomail,
                  'name'         => $toname,
                  'ccrecipients' => '',
                  'bccrecipients' => $bccrecipients,
                  'subject'      => $subject,
                  'message'      => $textmessage,
                  'htmlmessage'  => $htmlmessage,
                  'from'         => $fromemail,
                  'fromname'     => $fromname,
                  'usetemplates' => false);

   //don't throw a fit if there is an exception, just add a log message return and continue
    $usehtmlemail =  xarModGetVar('mail', 'html');
    if ($usehtmlemail != 1) {
        if (!xarModAPIFunc('mail','admin','sendmail', $args)) {
           xarLogMessage("Comments: error sending html notification mails");
        }
    } else {
        if (!xarModAPIFunc('mail','admin','sendhtmlmail', $args)) {
           xarLogMessage("Comments: error sending text notification mails");
        }
    }
    xarLogMessage("Comments: sent notification mails");

   return true;
}

?>