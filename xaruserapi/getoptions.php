<?php
/**
 * Comments module options
 *
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Grabs the list of viewing options in the following order of precedence:
 * 1. POST/GET
 * 2. User Settings (if user is logged in)
 * 3. Module Defaults
 * 4. internal defaults
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @author Jo Dalle Nogare (aka jojodee)
 * @access public
 * @returns array list of viewing options (depth, render style, order, and sortby)
 */
function comments_userapi_getoptions($args)
{
    if (!xarVarFetch('depth', 'int', $posted['depth'], xarModGetVar('comments','depth'), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('render', 'str', $posted['render'], xarModGetVar('comments','render'), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('order', 'int', $posted['order'], xarModGetVar('comments','order'), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('sortby', 'int', $posted['sortby'], xarModGetVar('comments','sortby'), XARVAR_NOT_REQUIRED)) return;

    if (!isset($args['itemtype'])) {
       $args['itemtype']= 0;
    } else {
        $args['itemtype'] = (int)$args['itemtype'];
    }
    //set all the defaults for vars that are by itemtype (not globals)
    $settings['usecommentnotify'] = xarModGetVar('comments','usecommentnotify');
    $settings['mustlogin']        = xarModGetVar('comments','mustlogin');
    $settings['showoptions']      = xarModGetVar('comments','showoptions');
    $settings['AllowPostAsAnon']  = xarModGetVar('comments','AllowPostAsAnon');
    $settings['edittimelimit']    = xarModGetVar('comments','edittimelimit');
    $settings['depth']            = xarModGetVar('comments','depth');
    $settings['render']           = xarModGetVar('comments','render');
    $settings['sortby']           = xarModGetVar('comments','sortby');
    $settings['order']            = xarModGetVar('comments','order');
    $settings['editstamp']        = xarModGetVar('comments','editstamp');
    $settings['wrap']             = xarModGetVar('comments','wrap');
    $settings['approvalrequired'] = xarModGetVar('comments','approvalrequired');
    $settings['approvalifprevious'] = xarModGetVar('comments','approvalifprevious');
    $settings['approvalifinfo']   = xarModGetVar('comments','approvalifinfo');
    $settings['holdallanon']        = xarModGetVar('comments','holdallanon');
    $settings['useavatars']       = xarModGetVar('comments','useavatars');
    $settings['manuallock']       = xarModGetVar('comments','manuallock');
    $settings['threadlock']       = xarModGetVar('comments','threadlock');
    $settings['threadlocktime']       = xarModGetVar('comments','threadlocktime');
    $settings['itemtimefield']       = xarModGetVar('comments','itemtimefield');
    $settings['quickpost']       = xarModGetVar('comments','quickpost');
    //do not include global vars here - only those that can be overridden

    //now check for itemtype and use that if it exists
    //need to discriminate between boolean vars and non-bool for correct assignment
    $boolvars = array('usercommentnotify','mustlogin','showoptions','AllowPostAsAnon','approvalrequired','approvalifprevious','holdallanon','useravatars','manuallock','threadlock','quickpost');
    // Get from hooked module if $args given
    if (isset($args['modid']) && xarModGetVar('comments','allowhookoverride') == 1) {
        $args['modname'] = xarModGetNameFromID($args['modid']);
        foreach ($settings as $k => $v) {
            //override with any hook setting if allowed
            if (in_array($k,$boolvars)) {
                 $hookedvar = xarModGetVar($args['modname'],$k . '.' .$args['itemtype']) ? 1 : 0;
            }else {
                 $hookedvar = xarModGetVar($args['modname'],$k . '.' .$args['itemtype']) ? xarModGetVar($args['modname'],$k . '.' .$args['itemtype']) : '';
            }
            $settings[$k] = $hookedvar;
        }
    }

   // Get from User Settings
    if (xarUserIsLoggedIn() && xarModGetVar('comments','usersetrendering') && !$settings['showoptions'] ) {
        foreach ($posted as $k => $v) {
            $uservar = xarModGetUserVar('comments', $k);
            if (!empty($uservar)) {
              $settings[$k] = $uservar;
            }
        }
    } elseif (xarUserIsLoggedIn() && $settings['showoptions'])  {
    foreach ($posted as $k => $v) {
              $settings[$k] = $v;
        }
    }

    return $settings;
}

?>