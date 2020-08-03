<?php
/**
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * update configuration for a module - hook for ('module','updateconfig','API')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
include_once('modules/comments/xarincludes/defines.php');
function comments_adminapi_updateconfighook($args)
{
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
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)','module name', 'admin', 'updateconfighook', 'categories');
       throw new BadParameterException(null,$msg);
    }

    // @todo check in $extrainfo (is it worth it?)
    if (!xarVarFetch('showoptions', 'checkbox', $settings['showoptions'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('AllowPostAsAnon', 'checkbox',  $settings['AllowPostAsAnon'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('depth', 'int:1:',  $settings['depth'], _COM_MAX_DEPTH, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('render', 'str:1:',  $settings['render'], _COM_VIEW_THREADED, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('sortby', 'str:1:',  $settings['sortby'], _COM_SORTBY_THREAD, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('order', 'str:1:',  $settings['order'], _COM_SORT_ASC, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('editstamp','int', $settings['editstamp'],0,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('wrap','checkbox',  $settings['wrap'], false,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('edittimelimit', 'str:1:',  $settings['edittimelimit'], '', XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('mustlogin', 'checkbox',  $settings['mustlogin'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('usecommentnotify', 'checkbox',  $settings['usecommentnotify'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('approvalrequired', 'checkbox',  $settings['approvalrequired'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('approvalifprevious', 'checkbox',  $settings['approvalifprevious'],false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('approvalifinfo', 'checkbox',  $settings['approvalifinfo'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('holdallanon', 'checkbox',  $settings['holdallanon'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('useavatars', 'int:0:',  $settings['useavatars'], 0, XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('manuallock', 'checkbox', $settings['manuallock'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('threadlock', 'checkbox', $settings['threadlock'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('threadlocktime','float',$settings['threadlocktime'],0,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('itemtimefield','str:0:', $settings['itemtimefield'],'',XARVAR_NOT_REQUIRED)) return;
     if (!xarVarFetch('quickpost','checkbox', $settings['quickpost'],false,XARVAR_NOT_REQUIRED)) return;
    $itemtype = 0;
    if (isset($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    }

    foreach ($settings as $k=>$v) {
        if ($modname =="comments") {
            xarModSetVar('comments', $k,  $settings[$k]);
        } else {
            xarModSetVar($modname, $k.'.' . $itemtype, $settings[$k]);
        }
    }

    return $extrainfo;
}
?>