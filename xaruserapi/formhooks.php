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
 * Sets up any formaction / formdisplay hooks
 *
 */
function comments_userapi_formhooks($args)
{
    if(!xarVarFetch('modname', 'isset',    $modname, '',     XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('itemtype', 'isset',    $itemtype, 0,     XARVAR_NOT_REQUIRED)) {return;}
    //let the args override if necessary

    if (!isset($itemtype) || empty($itemtype)) {
       $itemtype = '0';
    }

    $hooks = array();
    if (isset($modname) && !empty($modname)) {
        $args['modname'] = $modname;
    }
    //now get output from relevant functions
    $hooks['formaction']              = xarModCallHooks('item', 'formaction', '', $args, 'comments',$itemtype);
    $hooks['formdisplay']             = xarModCallHooks('item', 'formdisplay', '', $args, 'comments',$itemtype);
    $hooks['formnew']                 = xarModCallHooks('item', 'new', '', $args, 'comments',$itemtype);

    if (empty($hooks['formaction'])){
        $hooks['formaction'] = '';
    } elseif (is_array($hooks['formaction'])) {
        $hooks['formaction'] = join('',$hooks['formaction']);
    }

    if (empty($hooks['formdisplay']) ){
        $hooks['formdisplay'] = '';
    } elseif (is_array($hooks['formdisplay'])) {
        $hooks['formdisplay'] = join('',$hooks['formdisplay']);
    }

    if (empty($hooks['formnew'])){
        $hooks['formnew'] = '';
    } elseif (is_array($hooks['formnew'])) {
        $hooks['formnew'] = join('',$hooks['formnew']);
    }

    return $hooks;
}

?>