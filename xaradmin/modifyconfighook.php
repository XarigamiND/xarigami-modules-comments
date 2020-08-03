<?php
/**
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Modify configuration for a module - hook for ('module','modifyconfig','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function comments_admin_modifyconfighook($args)
{
    include_once 'modules/comments/xarincludes/defines.php';
    extract($args);

    if (!isset($extrainfo)) {
        $extrainfo = array();
    }

    // When called via hooks, the module name may be empty, so we get it from
    // the current module
    if (empty($extrainfo['module'])) {
        $modname = xarModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarModGetIDFromName($modname);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)', 'module name', 'admin', 'modifyconfighook', 'comments');
        throw new BadParameterException(null,$msg);
    }

    $itemtype = 0;
    if (isset($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    }

    $data = array();

    // Have we set any vars for this itemtype yet?
    // if not, get them from the default mod vars

    //get the main comment var
        $data['depth'] = xarModGetVar('comments','depth');
        $data['render'] = xarModGetVar('comments','render');
        $data['sortby'] = xarModGetVar('comments','sortby');
        $data['order'] = xarModGetVar('comments','order');
        $data['editstamp'] = xarModGetVar('comments','editstamp');
        $data['AllowPostAsAnon'] = xarModGetVar('comments','AllowPostAsAnon');
        $data['wrap'] = xarModGetVar('comments','wrap');
        $data['showoptions'] = xarModGetVar('comments','showoptions');
        $data['edittimelimit'] = xarModGetVar('comments','edittimelimit');
        $data['mustlogin'] = xarModGetVar('comments','mustlogin');
        $data['usecommentnotify'] = xarModGetVar('comments','usecommentnotify');
        $data['approvalrequired']  = xarModGetVar('comments','approvalrequired');
        $data['approvalifprevious']  = xarModGetVar('comments','approvalifprevious');
        $data['approvalifinfo']  = xarModGetVar('comments','approvalifinfo');
        $data['holdallanon']  = xarModGetVar('comments','holdallanon'); //hold all anon posts for approva
        //other
        $data['useavatars']  = xarModGetVar('comments','useavatars');
        //thread lock
        $data['manuallock'] =  xarModGetVar('comments','manuallock'); //allow manual thread locking per itemtype item
        $data['threadlock'] =  xarModGetVar('comments','threadlock'); //turn auto thread lock on
        $data['threadlocktime'] =  xarModGetVar('comments','threadlocktime'); //auto time in days from original item create time
        $data['itemtimefield'] =  xarModGetVar('comments','itemtimefield');
        $data['quickpost'] =  xarModGetVar('comments','quickpost');
        if ($modname != 'comments') {
            foreach ($data as $k=>$v) {
                $data[$k] = xarModGetVar($modname, $k.'.' . $itemtype);
            }
        }

    $data['modname'] = $modname;

    return xarTplModule('comments','admin','modifyconfighook', $data);
}
?>