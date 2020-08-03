<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
include_once('modules/comments/xarincludes/defines.php');
/**
 * This is a standard function to modify the configuration parameters of the
 * module
 */
function comments_admin_modifyconfig()
{
    $editstamp=xarModGetVar('comments','editstamp');
    $data['editstamp']       = !isset($editstamp) ? 1 :$editstamp;
    $data['menulinks'] = xarModAPIFunc('comments','admin','getmenulinks');
    // Security Check
    if(!xarSecurityCheck('Comments-Admin'))
        return;
    $numstats       = xarModGetVar('comments','numstats');
    $rssnumitems    = xarModGetVar('comments','rssnumitems');

    $mustlogin    = xarModGetVar('comments','mustlogin');
    if (!isset($mustlogin) || $mustlogin ==0) {
        $data['mustlogin'] = false; //was set at an integer at some time so check for 0
    } else {
        $data['mustlogin'] = true;
    }

    $data['dochildcount'] = xarModGetVar('comments','dochildcount');
    if (empty($rssnumitems)) {
        xarModSetVar('comments', 'rssnumitems', 25);
    }
    if (empty($numstats)) {
        xarModSetVar('comments', 'numstats', 100);
    }
    $data['notifyfromemail'] = xarModGetVar('comments','notifyfromemail');
    if (!isset($data['notifyfromemail'])) $data['notifyfromemail'] = xarModGetvar('mail','adminmail');
    $data['notifyfromname']=xarModGetVar('comments','notifyfromname');
    if (!isset($data['notifyfromname'])) $data['notifyfromname'] = xarModGetvar('mail','adminname');
    $data['usersetrendering']  = xarModGetVar('comments','usersetrendering');


    $data['authid'] = xarSecGenAuthKey();
    $data['hooks'] = xarModCallHooks('module', 'modifyconfig', 'comments',
                                       array('module' => 'comments'));

    $data['edittimelimit'] = xarModGetVar('comments','edittimelimit');
    $data['depth'] = xarModGetVar('comments','depth');
    $data['render'] = xarModGetVar('comments','render');
    $data['sortby'] = xarModGetVar('comments','sortby');
    $data['order'] = xarModGetVar('comments','order');
    $data['AllowPostAsAnon'] = xarModGetVar('comments','AllowPostAsAnon');
    $data['showoptions'] = xarModGetVar('comments','showoptions');
    $data['editstamp'] = xarModGetVar('comments','editstamp');
    $data['wrap'] = xarModGetVar('comments','wrap');
    $data['usecommentnotify']  = xarModGetVar('comments','usecommentnotify');
    //approval vars
    $data['approvalrequired']  = xarModGetVar('comments','approvalrequired'); //approval required prior to viewing post
    $data['approvalifprevious']  = xarModGetVar('comments','approvalifprevious'); //approval auto if prior approved post
    $data['approvalifinfo']  = xarModGetVar('comments','approvalifinfo'); //approval for anon if they supply name/email
    $data['holdallanon']  = xarModGetVar('comments','holdallanon'); //hold all anon posts for approval
    //other
    $data['useavatars']  = xarModGetVar('comments','useavatars'); //0-no, 1-if available

    //auto moderation vars : GLOBAL
    $data['moderatewords']  = xarModGetVar('comments','moderatewords'); //list of words used to tag comments for moderation
    $data['moderatelinks']  = xarModGetVar('comments','moderatelinks'); //hold post for moderation if it contains links
    $data['moderatelinknum']  = xarModGetVar('comments','moderatelinknum');  //post must contain 2 or more links for holding
    //blacklist
    $data['blacklistwords']  = xarModGetVar('comments','blacklistwords'); //liste of words used to tag comment as spam
    //thread locking
    $data['manuallock'] =  xarModGetVar('comments','manuallock'); //allow manual thread locking per itemtype item
    $data['threadlock'] =  xarModGetVar('comments','threadlock'); //turn auto thread lock on
    $data['threadlocktime'] =  xarModGetVar('comments','threadlocktime'); //auto time in days from original item create time
    $data['itemtimefield'] =  xarModGetVar('comments','itemtimefield');
    $data['quickpost'] =  xarModGetVar('comments','quickpost');
    return $data;
}
?>
