<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
sys::import('modules.comments.xarincludes.defines');

/***
 *
 * These functions are being saved in the event that I actually decide to use them at
 * some point - currently however, this functionality is being done using css.
 *
 ***
 *

 **
 * Collapse a comment branch and store the parent where
 * the collapsing begins in a uservar
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 *
function comments_userapi_collapse( )
{

    $headers = xarRequestGetVar('headers');
    $package = xarRequestGetVar('package');
    $receipt = xarRequestGetVar('receipt');
    $package['settings'] = xarModAPIFunc('comments','user','getoptions');

    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    if (xarUserIsLoggedIn()) {

        $branches = unserialize(xarModGetUserVar('comments','CollapsedBranches'));

        if (!array_key_exists($header['cid'], $branches)) {
            $branches[$header['cid']] = $header['cid'];
            xarModSetUserVar('comments','CollapsedBranches',serialize($branches));
        }
    }

    $args['header[modid]']               = $header['modid'];
    $args['header[itemtype]']            = $header['itemtype'];
    $args['header[objectid]']            = $header['objectid'];

    if (isset($header['selected_cid'])) {
        $args['header[selected_cid]']        = $header['selected_cid'];
    }

    if (isset($header['branchout'])) {
        $args['header[branchout]']           = $header['branchout'];
        $args['header[cid]']                 = $header['cid'];
    }

    $args['receipt[returnurl][encoded]'] = $receipt['returnurl']['encoded'];
    $args['receipt[returnurl][decoded]'] = $receipt['returnurl']['decoded'];

    $url = $args['receipt[returnurl][decoded]'];

    foreach ($args as $k=>$v) {
        $url .= "&amp;$k=$v";
    }

    xarResponseRedirect($url);
}


 **
 * Expand a previously collapsed branch
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 * @returns mixed description of return
 *
function comments_userapi_expand( )
{

    $headers = xarRequestGetVar('headers');
    $package = xarRequestGetVar('package');
    $receipt = xarRequestGetVar('receipt');
    $package['settings'] = xarModAPIFunc('comments','user','getoptions');

    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    if (xarUserIsLoggedIn()) {

        $branches = unserialize(xarModGetUserVar('comments','CollapsedBranches'));

        if (array_key_exists($header['cid'], $branches)) {
            unset($branches[$header['cid']]);
            xarModSetUserVar('comments','CollapsedBranches',serialize($branches));
        }
    }

    $args['header[modid]']               = $header['modid'];
    $args['header[itemtype]']            = $header['itemtype'];
    $args['header[objectid]']            = $header['objectid'];

    if (isset($header['selected_cid'])) {
        $args['header[selected_cid]']        = $header['selected_cid'];
    }

    if (isset($header['branchout'])) {
        $args['header[branchout]']           = $header['branchout'];
        $args['header[cid]']                 = $header['cid'];
    }

    $args['receipt[returnurl][encoded]'] = $receipt['returnurl']['encoded'];
    $args['receipt[returnurl][decoded]'] = $receipt['returnurl']['decoded'];

    $url = $args['receipt[returnurl][decoded]'];

    foreach ($args as $k=>$v) {
        $url .= "&amp;$k=$v";
    }

    xarResponseRedirect($url);
}

*/
?>