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
 * Modify a comment, dependant on the following criteria:
 * 1. user is the owner of the comment, or
 * 2. user has a minimum of moderator permissions for the
 *    specified comment
 * 3. We haven't reached the edit time limit if it is set
 *
 * @access private
 * @returns mixed description of return
 */
function comments_user_modify()
{
    $header                       = xarRequestGetVar('header');
    $package                      = xarRequestGetVar('package');
    $receipt                      = xarRequestGetVar('receipt');
    $receipt['post_url']          = xarModURL('comments','user','modify');
    $header['input-title']        = xarML('Modify Comment');

    if (!xarVarFetch('cid', 'int:1:', $cid, 0, XARVAR_NOT_REQUIRED)) return;
    if (!empty($cid)) {
        $header['cid'] = $cid;
    }

    $comments = xarModAPIFunc('comments','user','get_one', array('cid' => $header['cid']));
    $author_id = $comments[0]['xar_uid'];

    if ($author_id != xarUserGetVar('uid')) {
        if (!xarSecurityCheck('Comments-Edit'))
            return;
    }

    if (!isset($package['postanon'])) {
        $package['postanon'] = 0;
    }
    xarVarValidate('checkbox', $package['postanon']);
    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    $header['modid'] = $comments[0]['xar_modid'];
    $header['itemtype'] = $comments[0]['xar_itemtype'];
    $header['objectid'] = $comments[0]['xar_objectid'];
    $module= xarModGetNameFromID($header['modid']);
    if (empty($receipt['action'])) {
        $receipt['action'] = 'modify';
    }

    // get the title and link of the original object
    $modinfo = xarModGetInfo($header['modid']);
    $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                               array('itemtype' => $header['itemtype'],
                                     'itemids' => array($header['objectid'])),
                               // don't throw an exception if this function doesn't exist
                               0);
    if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
        $url = $itemlinks[$header['objectid']]['url'];
        $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
        $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
    } else {
        $url = xarModURL($modinfo['name'],'user','main');
    }
    if (empty($receipt['returnurl'])) {
        $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                      'decoded' => $url);
    }

    $package['settings'] = xarModAPIFunc('comments','user','getoptions',$header);
    $editstamp = $package['settings']['editstamp']?$package['settings']['editstamp'] : 0;
    $anonid= xarConfigGetVar('Site.User.AnonymousUID');
    switch (strtolower($receipt['action'])) {
        case 'submit':
            //handle the user errors
            if (empty($package['title'])) {
                $invalid['title'] = xarML('You must enter a title');
            }

            if (empty($package['text'])) {
             $invalid['text'] = xarML('Please enter the text of your comment');
            }
            // call transform input hooks
             $package['transform'] = array($package['text'], $package['title']);
             $package = xarModCallHooks('item', 'transform-input', 0, $package,xarModGetNameFromID($header['modid']), $header['itemtype']);

            //we need to get info from create/update hook here for captchas,
            //but it is too early for any other type of create/update hook
            if (xarUserGetVar('uid') == xarConfigGetVar('Site.User.AnonymousUID')) {
                //prepare the postanon field
                $temp = array('name'=>$package['aname'],'email'=>$package['aemail'], 'web'=>$package['aweb']);
                if (isset($package['settings']['approvalifinfo']) &&  ($package['settings']['approvalifinfo']==1)) {
                    //test to ensure they have supplied email and name
                    $ntest = xarVarValidate('str:2:',$package['aname'],true);
                    $etest = xarVarValidate('email',$package['aemail'],true);
                    if (!$ntest)  $invalid['name'] = xarML('You must supply your name to post a comment');
                    if (!$etest)  $invalid['email'] = xarML('You must supply a valid email to post a comment');
                }
                $package['postanon'] = serialize($temp);
            }

            if (empty($package['settings']['edittimelimit'])
               or (time() <= ($comments[0]['xar_date'] + ($package['settings']['edittimelimit'] * 60)))
               or xarSecurityCheck('Comments-Admin', 0)) {

               $package = xarModCallHooks('item', 'transform', 0, $package,
                                       $module, $header['itemtype']);
                xarModAPIFunc('comments','user','modify',
                                        array('cid'      => $header['cid'],
                                              'text'     => $package['text'],
                                              'title'    => $package['title'],
                                              'postanon' => $package['postanon'],
                                              'editstamp' => $editstamp,
                                              'authorid' => $author_id));
            }

            xarResponseRedirect($receipt['returnurl']['decoded']);
            return true;


        case 'modify':
            $htmltext            = xarVarPrepHTMLDisplay($comments[0]['xar_text']);
            $htmltitle           = xarVarPrepHTMLDisplay($comments[0]['xar_title']);

            list($comments[0]['transformed-text'],
                 $comments[0]['transformed-title']) = xarModCallHooks('item','transform', $header['cid'],
                                         array($htmltext,$htmltitle),
                                        'comments',$header['itemtype']);


            $package['transformed-text']        = $comments[0]['transformed-text'];
            $package['transformed-title']       = $comments[0]['transformed-title'];
            $package['comments'][0]['xar_cid']  = $header['cid'];
            $package['title']                   = $comments[0]['xar_title'];
            $package['text']                    = $comments[0]['xar_text'];

            $receipt['action']                  = 'modify';

            $output['header']                   = $header;
            $output['package']                  = $package;
            $output['receipt']                  = $receipt;
            $anoninfo = '';
            if (isset($postanon) && ($comments[0]['xar_uid'] == $anonuid)) {
                $postanon = unserialize($comments[0]['xar_postanon']);
                $anoninfo['aname'] = isset($postanon['name']) ? $postanon['name']:'';
                $package['aname'] = $anoninfo['aname'] ;
                $anoninfo['aemail']  = isset($postanon['email'])? $postanon['email']:'';
                $package['aemail'] = $anoninfo['aemail'] ;
                $anoninfo['aweb'] = isset($postanon['web'])? $postanon['web']:'';
                $package['aweb'] = $anoninfo['aweb'] ;
                $comments[0]['anoninfo'] = $anoninfo;
             } else {
                $package['aname'] ='';
                $package['aemail'] ='';
                $package['aweb'] ='';
             }
            $package['comments']                = $comments;
            break;
        case 'preview':
        default:
            //jojo - order is important here for transform and text preping
            $package['htmltext']              = xarVarPrepHTMLDisplay($package['text']);
            $package['htmltitle']             = xarVarPrepHTMLDisplay($package['title']);

            list($package['transformed-text'],
                 $package['transformed-title']) = xarModCallHooks('item',
                                                                  'transform',
                                                                  $header['pid'],
                                                                  array($package['htmltext'],
                                                                        $package['htmltitle']),'comments', $header['itemtype']);//,xarModGetNameFromID($header['modid']), $header['itemtype']);

            $comments[0]['xar_modid']    = $header['modid'];
            $comments[0]['xar_itemtype'] = $header['itemtype'];
            $comments[0]['xar_objectid'] = $header['objectid'];
            $comments[0]['xar_pid']      = $header['pid'];
            $comments[0]['xar_author']   = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous');
            $comments[0]['xar_cid']      = 0;
            $comments[0]['xar_postanon'] = $package['postanon'];
            $comments[0]['xar_date']     = time();
            $comments[0]['transformed-title']    = $package['transformed-title'];
            $comments[0]['transformed-text']    = $package['transformed-text'];
            $comments[0]['title']    = $package['title'];
            $comments[0]['text']    = $package['text'];
            $hostname = xarSessionGetIPAddress();

            $comments[0]['xar_hostname'] = $hostname;
            $package['comments']         = $comments;
            $receipt['action']           = 'modify';

            break;

    }

    $hooks = xarModAPIFunc('comments','user','formhooks');

/*
    // Call modify hooks for categories, dynamicdata etc.
    $args['module'] = 'comments';
    $args['itemtype'] = 0;
    $args['itemid'] = $header['cid'];
    // pass along the current module & itemtype for pubsub (urgh)
// FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
    $args['cid'] = 0; // dummy category
    $modinfo = xarModGetInfo($header['modid']);
    $args['current_module'] = $modinfo['name'];
    $args['current_itemtype'] = $header['itemtype'];
    $args['current_itemid'] = $header['objectid'];
    $hooks['iteminput'] = xarModCallHooks('item', 'modify', $header['cid'], $args);
*/
    /*  Generate a onetime authorisation code for this operation */
    $package['authid'] = xarSecGenAuthKey('comments');
    $package['isanon'] = $anonid;
    $output['hooks']              = $hooks;
    $output['header']             = $header;
    $output['package']            = $package;
    $output['package']['date']    = time();
    $output['package']['uid']     = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uid') : 2);
    $output['package']['uname']   = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uname') : 'anonymous');
    $output['package']['name']    = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous');
    $output['receipt']            = $receipt;


    return $output;

}
?>