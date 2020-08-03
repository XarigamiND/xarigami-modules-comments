<?php
/**
 * Update comment module configuration
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
sys::import('modules.comments.xarincludes.defines');
/**
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 */
function comments_admin_updateconfig()
{
    if (!xarSecConfirmAuthKey()) return;
    if (!xarSecurityCheck('Comments-Admin')) return;

    if (!xarVarFetch('AllowPostAsAnon', 'checkbox',  $AllowPostAsAnon, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('showoptions', 'checkbox', $showoptions, false, XARVAR_NOT_REQUIRED)) return;
   // if (!xarVarFetch('postanon', 'checkbox', $postanon, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('depth', 'int:1:', $depth, _COM_MAX_DEPTH, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('render', 'str:1:', $render, _COM_VIEW_THREADED, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('sortby', 'str:1:', $sortby, _COM_SORTBY_THREAD, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('order', 'str:1:', $order, _COM_SORT_ASC, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('editstamp','int:0:2',$editstamp,0,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('wrap','checkbox', $wrap, false,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('edittimelimit','str:1:', $edittimelimit, '',XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('authorize', 'checkbox', $authorize, false, XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('numstats', 'int', $numstats, 100, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('rssnumitems', 'int', $rssnumitems, 25, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('showtitle', 'checkbox', $showtitle, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('allowhookoverride', 'checkbox', $allowhookoverride, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('mustlogin', 'checkbox', $mustlogin, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('usersetrendering', 'checkbox', $usersetrendering, false, XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('usecommentnotify', 'checkbox', $usecommentnotify, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('dochildcount', 'checkbox', $dochildcount, true, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('notifyfromemail', 'str:1:', $notifyfromemail, xarModGetVar('mail','adminmail'), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('notifyfromname', 'str:1:', $notifyfromname, xarModGetVar('mail','adminname'), XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('approvalrequired', 'checkbox', $approvalrequired, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('approvalifprevious', 'checkbox', $approvalifprevious,false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('approvalifinfo', 'checkbox', $approvalifinfo, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('holdallanon', 'checkbox', $holdallanon, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('useavatars', 'int:0:', $useavatars, 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('manuallock', 'checkbox', $manuallock, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('threadlock', 'checkbox', $threadlock, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('threadlocktime','float',$threadlocktime,0,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('itemtimefield','str:0:',$itemtimefield,'',XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('quickpost','checkbox',$quickpost,false,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('moderatewords', 'str', $moderatewords, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('moderatelinks', 'checkbox', $moderatelinks, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('moderatelinknum', 'int:0:', $moderatelinknum,2, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('blacklistwords', 'str', $blacklistwords,'', XARVAR_NOT_REQUIRED)) return;

    xarModSetVar('comments', 'mustlogin', $mustlogin);
    xarModSetVar('comments', 'dochildcount', $dochildcount);
    xarModSetVar('comments', 'edittimelimit', $edittimelimit);
    xarModSetVar('comments', 'AllowPostAsAnon',$AllowPostAsAnon);
    xarModSetVar('comments', 'AuthorizeComments', $authorize);
    xarModSetVar('comments', 'depth', $depth);
    xarModSetVar('comments', 'render', $render);
    xarModSetVar('comments', 'sortby', $sortby);
    xarModSetVar('comments', 'order', $order);
    xarModSetVar('comments', 'editstamp', $editstamp);
    xarModSetVar('comments', 'wrap', $wrap);
    xarModSetVar('comments', 'showoptions', $showoptions);

    xarModSetVar('comments', 'numstats', $numstats);
    xarModSetVar('comments', 'rssnumitems', $rssnumitems);
    xarModSetVar('comments', 'showtitle', $showtitle);

    xarModSetVar('comments','usersetrendering',$usersetrendering);
    xarModSetVar('comments', 'allowhookoverride', $allowhookoverride);
    xarModSetVar('comments','usecommentnotify',$usecommentnotify);
    xarModSetVar('comments','notifyfromemail',$notifyfromemail);
    xarModSetVar('comments','notifyfromname',$notifyfromname);

    xarModSetVar('comments','approvalrequired',$approvalrequired);
    xarModSetVar('comments','approvalifprevious',$approvalifprevious);
    xarModSetVar('comments','approvalifinfo',$approvalifinfo);
    xarModSetVar('comments','holdallanon',$holdallanon);
    //other
    xarModSetVar('comments','useavatars',$useavatars);

    xarModSetVar('comments','manuallock',$manuallock); //allow manual thread locking per itemtype item
    xarModSetVar('comments','threadlock',$threadlock); //turn auto thread lock on
    xarModSetVar('comments','threadlocktime',$threadlocktime); //auto time in days from original item create time
    xarModSetVar('comments','itemtimefield',$itemtimefield); //datestamp field for autotimel
    xarModSetVar('comments','quickpost',$quickpost);
    //auto moderation vars : GLOBAL
    xarModSetVar('comments','moderatewords',$moderatewords);
    xarModSetVar('comments','moderatelinks',$moderatelinks);
    xarModSetVar('comments','moderatelinknum',$moderatelinknum);
    //blacklist
    xarModSetVar('comments','blacklistwords',$blacklistwords);

    xarModCallHooks('module', 'updateconfig', 'comments', array('module' => 'comments'));

     if ($usersetrendering == true || $usecommentnotify == true || $showoptions) {
     //check and hook Comments to roles if not already hooked
         if (!xarModIsHooked('comments', 'roles')) {
             xarModAPIFunc('modules','admin','enablehooks',
                                 array('callerModName' => 'roles',
                                       'hookModName' => 'comments'));
         }

     } else {
       if (xarModIsHooked('comments', 'roles')) {
        //unhook Comments from roles
             xarModAPIFunc('modules','admin','disablehooks',
                                 array('callerModName' => 'roles',
                                       'hookModName' => 'comments'));
          }
     }
    $msg = xarML('Comment configuration was successfully updated');
    xarTplSetMessage($msg,'status');
    xarResponseRedirect(xarModURL('comments', 'admin', 'modifyconfig'));
    return true;
}
?>