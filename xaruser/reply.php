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
/**
 * processes comment replies and then redirects back to the
 * appropriate module/objectid (aka page)
 * @access   public
 * @returns  array      returns whatever needs to be parsed by the BlockLayout engine
 */

function comments_user_reply()
{

    if (!xarSecurityCheck('Comments-Post'))
        return;

    if (!xarVarFetch('return_url', 'str:1', $return_url, NULL, XARVAR_NOT_REQUIRED)) {return;}
    //for comment reply only
    if (!xarVarFetch('notifyme', 'checkbox', $notifyme, false, XARVAR_NOT_REQUIRED)) {return;}

    $antibotinvalid = 0; //initialize

    $header                       = xarRequestGetVar('header');
    $package                      = xarRequestGetVar('package');
    $receipt                      = xarRequestGetVar('receipt');
    $receipt['post_url']          = xarModURL('comments','user','reply');
    $header['input-title']        = xarML('Post a reply');

    if (!isset($package['postanon'])) {
        $package['postanon'] = '0';
    }
    xarVarValidate('checkbox', $package['postanon']);
    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    if (empty($receipt['action'])) {
        $receipt['action'] = 'reply';
    }

    $modname = isset($header['modid'])?xarModGetNameFromID($header['modid']):'';
    //we need this info early - if a problem move back to
    $package['settings'] = xarModAPIFunc('comments','user','getoptions',$header);
    $anonuid = xarConfigGetVar('Site.User.AnonymousUID');

    $receipt['preview'] = false;
    switch (strtolower($receipt['action'])) {
        case 'submit':
        /* Confirm authorisation code.
        */
        if (!xarSecConfirmAuthKey('comments')) return;

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

        $args = array('modid'    => $header['modid'],
                     'itemtype' => $header['itemtype'],
                     'objectid' => $header['objectid'],
                     'pid'      => $header['pid'],
                     'comment'  => $package['text'],
                     'title'    => $package['title'],
                     'postanon' => $package['postanon'],
                     'invalid' =>  isset($invalid)?$invalid:array()
                   //  'antibotcode' => isset($antibotcode)?$antibotcode:''
                     );

        $hookinfo = xarModCallHooks('item', 'submit', $header['objectid'], $args);
        $antibotinvalid = isset($hookinfo['antibotinvalid']) ? $hookinfo['antibotinvalid'] : 0;

        if ($antibotinvalid != TRUE && empty($invalid)) {
            $newcommentid = xarModAPIFunc('comments','user','add', $args);
            $idheld = xarSessionGetVar('comments.held');
            xarSessionDelVar('comments.held'); //delete this session var
            //notifications
            //should only notify when a comment is 'approved' ie _COM_STATUS_ON
            $currentuser = xarUserGetVar('uid');
            $msg = xarML('Your comment was successfully added.');

            if (is_int($newcommentid) && ($newcommentid != $idheld) && ($notifyme == TRUE)) {
                xarModAPIFunc('comments','user','setnotify',array('parent_id'=>$newcommentid,'user_id'=>$currentuser,'module_id'=>$header['modid'],'object_id'=>$header['objectid'],'itemtype'=>$header['itemtype'],'notifytype'=>2));
            }
            //prepare status message
            if ($newcommentid == $idheld) { //flag for moderation
                $msg .= xarML(' It is currently queued for moderator approval before publishing');
                //we don't need the next line anymore now  (keep for now and mark for deprecation).
                $receipt['returnurl']['decoded'] = $receipt['returnurl']['decoded'].'&amp;rp=1';
            }
             xarTplSetMessage($msg,'status');

            xarResponseRedirect($receipt['returnurl']['decoded']);

            return true;
        }

        $package['comments'] = isset($package['comments'])?$package['comments']:array();
        break;

        case 'reply':
            if(!array_key_exists('pid', $header) || !is_numeric($header['pid'])) {
                $msg = xarML('Submitting a comment reply failed because the function was not invoked from a previous comment to reply to.');
                return xarTplModule('base','user','errors',array('errortype' => 'caught','var1'=>$msg));
            }

            $comments = xarModAPIFunc('comments','user','get_one',
                                       array('cid' => $header['pid']));


            if (preg_match('/^(re\:|re\([0-9]+\))/i',$comments[0]['xar_title'])) {
                if (preg_match('/^re\:/i',$comments[0]['xar_title'])) {
                    $new_title = preg_replace("/re\:/i",
                                              'Re(1):',
                                              $comments[0]['xar_title'],
                                              1
                                             );
                } else {
                    preg_match('/^re\(([0-9]+)?/i',$comments[0]['xar_title'], $matches);
                    $new_title = preg_replace("/re\([0-9]+\)\:/i",
                                              'Re('.($matches[1] + 1).'):',
                                              $comments[0]['xar_title'],
                                              1
                                             );
                }
            } else {
                $new_title = 'Re: '.$comments[0]['xar_title'];
            }

            $header['modid'] = $comments[0]['xar_modid'];
            $header['itemtype'] = $comments[0]['xar_itemtype'];
            $header['objectid'] = $comments[0]['xar_objectid'];

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

            //jojo - order is important for prep and transform
            $package['htmltext']        = xarVarPrepHTMLDisplay($comments[0]['xar_text']);
            $package['htmltitle']        = xarVarPrepHTMLDisplay($comments[0]['xar_title']);

            //now transform
            list($comments[0]['transformed-text'],
                 $comments[0]['transformed-title']) = xarModCallHooks('item','transform',$header['pid'],
                                         array( $package['htmltext'],$package['htmltitle']), 'comments');
                                        // xarModGetNameFromID($header['modid']),$header['itemtype']);


            $package['new_title']            = xarVarPrepForDisplay($new_title);

            $package['text']                 = $comments[0]['xar_text'] ;
            $package['title']                = $comments[0]['xar_title'] ;
            $package['transformed-text']     = $comments[0]['transformed-text'];
            $package['transformed-title']    = $comments[0]['transformed-title'];

            $receipt['action']               = 'reply';
            $output['header']                = $header;
            $output['package']               = $package;
            $output['receipt']               = $receipt;

            $postanon  =   $comments[0]['xar_postanon'];

            //for anonymous - let's check
            $package['isanon'] = $anonuid;
            $anoninfo = '';
            //<jojo> we have two situations here
            //1. Anon info used to display any given comment in a list, or in a post reply - the comment replied to
            //2. The anon info used to populate a new post or new reply

            // some variables for any new anon post
                $package['aname'] ='';
                $package['aemail'] ='';
                $package['aweb'] ='';
            //here we do the $anoninfo variable with all existing post info passed as $package['anoninfo']
            if (isset($postanon) && ($comments[0]['xar_uid'] == $anonuid)) {
                $postanon = unserialize($comments[0]['xar_postanon']);
                $anoninfo['aname'] = isset($postanon['name']) ? $postanon['name']:'';
                $anoninfo['aemail']  = isset($postanon['email'])? $postanon['email']:'';
                $anoninfo['aweb'] = isset($postanon['web'])? $postanon['web']:'';
                $comments[0]['anoninfo'] = $anoninfo;
             }
             $package['comments'] = $comments;
        break;

        case 'preview':

        default:
            /* Confirm authorisation code.
             Remember that some mods can call apis directly and handle their own form submits
             And sometimes comments is used by itself without hooks or other modules.
             Let's just focus on comments as hooks and comments itself for now
           */
           if (!xarSecConfirmAuthKey('comments')) return;


           //jojo - order is important here for transform and text preping
            $package['htmltext']  = xarVarPrepHTMLDisplay($package['text']);

            $package['htmltitle'] = xarVarPrepHTMLDisplay($package['title']);

            list($package['transformed-text'],
                 $package['transformed-title']) = xarModCallHooks('item','transform', $header['pid'],
                                                      array($package['htmltext'],
                                                            $package['htmltitle']));//,xarModGetNameFromID($header['modid']),$header['itemtype']);

            $comments[0]['transformed-text']  = $package['transformed-text'];
            $comments[0]['transformed-title'] = $package['transformed-title'];

            $package['xar_text']  = $package['text'];
            $package['xar_title'] = $package['title'];

            $comments[0]['xar_modid']     = $header['modid'];
            $comments[0]['xar_itemtype']  = $header['itemtype'];
            $comments[0]['xar_objectid']  = $header['objectid'];
            $comments[0]['xar_pid']       = $header['pid'];
            $comments[0]['xar_author']    = (xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous';
            $comments[0]['xar_uid']     = (xarUserIsLoggedIn()) ? xarUserGetVar('uid') : xarConfigGetVar('Site.User.AnonymousUID');
            $comments[0]['xar_cid']       = 0;
            $comments[0]['xar_postanon']  = $package['postanon'];
            $comments[0]['xar_date']      = time();
            $comments[0]['xar_hostname']  = 'somewhere';

            $package['new_title']         = $package['title'];
            $receipt['action']            = 'reply';
            $receipt['preview']            = true;
            if ($comments[0]['xar_uid'] == xarConfigGetVar('Site.User.AnonymousUID')) {
                //prepare the postanon field

                $temp = array('name'=>$package['aname'],'email'=>$package['aemail'], 'web'=>$package['aweb']);
                if (isset($package['settings']['approvalifinfo']) &&  ($package['settings']['approvalifinfo']==1)) {
                    //test to ensure they have supplied email and name
                    $ntest = xarVarValidate('str:2:',$package['aname'],true);
                    $etest = xarVarValidate('email',$package['aemail'],true);
                    if (!$ntest)  $invalid['name'] = xarML('You must supply your name to post a comment');
                    if (!$etest)  $invalid['email'] = xarML('You must supply a valid email to post a comment');
                }
                $package['anoninfo'] = $temp;
                $comments[0]['anoninfo'] = $temp;
            } else {
                $package['aname'] ='';
                $package['aemail'] ='';
                $package['aweb'] ='';
            }

            $package['comments']  = $comments;

    }

    // Language strings for JS form validator
    $package['formcheckmsg'] = xarML('Please complete the following fields:');
    $package['titlelabel'] = xarML('Subject');
    $package['textlabel'] = xarML('Comment');

    //formcaptcha
    $package['casmsg']= isset($casmsg)?$casmsg:'';

    $hooks = xarModAPIFunc('comments','user','formhooks',array('modname'=>'comments','itemtype'=>'0','antibotinvalid'=>$antibotinvalid));


/*
    // Call new hooks for categories, dynamicdata etc.
    $args['module'] = 'comments';
    $args['itemtype'] = 0;
    $args['itemid'] = 0;
    // pass along the current module & itemtype for pubsub (urgh)
// FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
    $args['cid'] = 0; // dummy category
    $modinfo = xarModGetInfo($header['modid']);
    $args['current_module'] = $modinfo['name'];
    $args['current_itemtype'] = $header['itemtype'];
    $args['current_itemid'] = $header['objectid'];
    $hooks['iteminput'] = xarModCallHooks('item', 'new', 0, $args);
*/
    /*  Generate a onetime authorisation code for this operation */
    $package['authid'] = xarSecGenAuthKey('comments');

 //   $package['settings'] = xarModAPIFunc('comments','user','getoptions',$header);


    $output['hooks']              = $hooks;
    $output['header']             = $header;
    $output['package']            = $package;
    $output['package']['date']    = time();
    $output['package']['isanon']  = $anonuid;
    $output['package']['uid']     = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uid') : $anonuid);
    $output['package']['uname']   = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uname') : 'anonymous');
    $output['package']['name']    = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous');
    $output['receipt']            = $receipt;

    $output['invalid'] = isset($invalid)?$invalid:array();
    return $output;

}

?>
