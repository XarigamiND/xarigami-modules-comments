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
 * Displays a comment or set of comments
 * @access   public
 * @param    integer    $args['modid']          the module id
 * @param    integer    $args['itemtype']       the item type
 * @param    string     $args['objectid']       the item id
 * @param    string     $args['returnurl']      the url to return to
 * @param    integer    $args['depth']          depth of comment thread to display
 * @param    integer    $args['selected_cid']   optional: the cid of the comment to view (only for displaying single comments)
 * @param    integer    $args['thread']         optional: display the entire thread following cid
 * @param    integer    $args['preview']        optional: an array containing a single (preview) comment used with adding/editing comments
 * @return  array      returns whatever needs to be parsed by the BlockLayout engine
 */
function comments_user_display($args)
{
    if (!xarSecurityCheck('Comments-Read', 0)) return;
    if (!xarVarFetch('quickpost', 'int:0:1', $quickpost, 0, XARVAR_GET_OR_POST)) {return;}
    if (!xarVarFetch('rp', 'int:0:1', $rp, 0, XARVAR_GET_OR_POST)) {return;} //return after a post
    // check if we're coming via a hook call
    if (isset($args['objectid'])) {
        $ishooked = 1;
    } else {
        // if we're not coming via a hook call
        $ishooked = 0;
        // then check for a 'cid' parameter
        if (!empty($args['cid'])) {
            $cid = $args['cid'];
        } else {
            xarVarFetch('cid', 'int:1:', $cid, 0, XARVAR_NOT_REQUIRED);
        }
        // and set the selected cid to this one
        if (!empty($cid) && !isset($args['selected_cid'])) {
            $args['selected_cid'] = $cid;
        }
    }

    $header   = xarRequestGetVar('header');
    $package  = xarRequestGetVar('package');
    $receipt  = xarRequestGetVar('receipt');

    // Fetch the module ID
    if (isset($args['modid'])) {
        $header['modid'] = $args['modid'];
    } elseif (isset($header['modid'])) {
        $args['modid'] = $header['modid'];
    } elseif (!empty($args['extrainfo']) && !empty($args['extrainfo']['module'])) {
        if (is_numeric($args['extrainfo']['module'])) {
            $modid = $args['extrainfo']['module'];
        } else {
            $modid = xarModGetIDFromName($args['extrainfo']['module']);
        }
        $args['modid'] = $modid;
        $header['modid'] = $modid;
    } else {
        xarVarFetch('modid', 'isset', $modid, NULL, XARVAR_NOT_REQUIRED);
        if (empty($modid)) {
            $modid = xarModGetIDFromName(xarModGetName());
        }
        $args['modid'] = $modid;
        $header['modid'] = $modid;
    }

    $header['modname'] = xarModGetNameFromID($header['modid']);

    // Fetch the itemtype
    if (isset($args['itemtype'])) {
        $header['itemtype'] = $args['itemtype'];
    } elseif (isset($header['itemtype'])) {
        $args['itemtype'] = $header['itemtype'];
    } elseif (!empty($args['extrainfo']) && isset($args['extrainfo']['itemtype'])) {
        $args['itemtype'] = $args['extrainfo']['itemtype'];
        $header['itemtype'] = $args['extrainfo']['itemtype'];
    } else {
        xarVarFetch('itemtype', 'isset', $itemtype, NULL, XARVAR_NOT_REQUIRED);
        $args['itemtype'] = $itemtype;
        $header['itemtype'] = $itemtype;
    }

    $package['settings'] = xarModAPIFunc('comments','user','getoptions',$header);

    // FIXME: clean up return url handling

    $settings_uri = "&amp;depth={$package['settings']['depth']}"
        . "&amp;order={$package['settings']['order']}"
        . "&amp;sortby={$package['settings']['sortby']}"
        . "&amp;render={$package['settings']['render']}";

    // Fetch the object ID if it is set (this is coming from a hook)
    if (isset($args['objectid'])) {
        $header['objectid'] = $args['objectid'];
    } elseif (isset($header['objectid'])) {
        $args['objectid'] = $header['objectid'];
    } else {
        xarVarFetch('objectid','isset', $objectid, NULL, XARVAR_NOT_REQUIRED);
        $args['objectid'] = $objectid;
        $header['objectid'] = $objectid;
    }

    if (isset($args['selected_cid'])) {
        $header['selected_cid'] = $args['selected_cid'];
    } elseif (isset($header['selected_cid'])) {
        $args['selected_cid'] = $header['selected_cid'];
    } else {
        xarVarFetch('selected_cid', 'isset', $selected_cid, NULL, XARVAR_NOT_REQUIRED);
        $args['selected_cid'] = $selected_cid;
        $header['selected_cid'] = $selected_cid;
    }
    if (!isset($args['thread'])) {
        xarVarFetch('thread', 'isset', $thread, NULL, XARVAR_NOT_REQUIRED);
    }

    if (isset($thread) && ($thread==1)) {
        $header['cid'] = (int)$cid;
    }

    if (!xarModLoad('comments','renderer')) {
        throw new FunctionNotFoundException('comments_renderer');
    }

    if (!isset($header['selected_cid']) || isset($thread)) {
        $package['comments'] = xarModAPIFunc('comments','user','get_multiple',$header);

        if (count($package['comments']) > 1) {
            $package['comments'] = comments_renderer_array_sort(
                $package['comments'],
                $package['settings']['sortby'],
                $package['settings']['order']
            );
        }
    } else {
        $header['cid'] = $header['selected_cid'];
        $package['settings']['render'] = _COM_VIEW_FLAT;
        $package['comments'] = xarModAPIFunc('comments','user','get_one', $header);
        if (!empty($package['comments'][0])) {
            $header['modid'] = $package['comments'][0]['xar_modid'];
            $header['itemtype'] = $package['comments'][0]['xar_itemtype'];
            $header['objectid'] = $package['comments'][0]['xar_objectid'];
        }
    }


    $package['comments'] = comments_renderer_array_prune_excessdepth(
                    array(
                            'array_list'    => $package['comments'],
                            'cutoff'        => $package['settings']['depth'],
                            'modid'         => $header['modid'],
                            'itemtype'      => $header['itemtype'],
                            'objectid'      => $header['objectid'],
                        )
                    );


    $moderator = FALSE;
    if (xarSecurityCheck('CommentThreadModerator',0,'Thread',"{$header['modid']}:{$header['itemtype']}:{$header['objectid']}")) $moderator = TRUE;

    //get the root node of the cid
    $rootinfo = xarModAPIFunc('comments','user','get_node_root',
                    array(
                            'modid'         => $header['modid'],
                            'itemtype'      => $header['itemtype'],
                            'objectid'      => $header['objectid'],
                          )
                        );

    if (empty($package['comments']) && (($package['settings']['threadlock'])||($package['settings']['manuallock'] && $moderator))) {
        //check for a root cid and if not - create the root cid

       if  (!count($rootinfo)) {
           $header['rootcid'] = xarModAPIFunc('comments','user','add_rootnode',
                                      array('modid'    => $header['modid'],
                                            'objectid' => $header['objectid'],
                                            'itemtype' => $header['itemtype']));
            $header['nodestatus']= _COM_STATUS_ROOT_NODE;
        } else {
            $header['nodestatus']= $rootinfo['xar_status'];
            $header['rootcid']= $rootinfo['xar_cid'];
        }
    } elseif (!empty($package['comments'])) {
        $header['nodestatus']= $rootinfo['xar_status'];
        $header['rootcid']= $rootinfo['xar_cid'];
    } else {
        $header['nodestatus']= _COM_STATUS_ROOT_NODE;
    }

    //if thread not already locked, check to see if it is time locked
    $package['timelocked'] = false;
    if (($header['nodestatus'] != _COM_STATUS_NODE_LOCKED) && $package['settings']['threadlock'] && ($package['settings']['threadlocktime'] > 0) && (!empty($package['settings']['itemtimefield']))) {
        //check the lock since item creation time
        $parentmod = xarModGetNameFromID($header['modid']);
        $parentinfo = xarModAPIFunc($parentmod,'user','get',array('itemtype'=>$header['itemtype'],'itemid'=>$header['objectid']));
        $timefield = $package['settings']['itemtimefield'];
        if ($parentinfo && is_array($parentinfo)) {
            //in case we have a comma separated list
            $timefields = explode (',',$timefield);
            if (is_array($timefields)) {
                foreach ($timefields as $testfield) {
                    if (isset($parentinfo[$testfield])) {
                        $timetolock = $parentinfo[$testfield] + ($package['settings']['threadlocktime'] * 60 * 60 * 24);
                        if (time() > $timetolock) {
                          //set the thread as locked
                           $setstatus = xarModAPIFunc('comments','admin','setstatus',
                                 array('cid'=>$header['rootcid'],'status'=>_COM_STATUS_NODE_LOCKED, 'modid'=>$header['modid'],'itemtype'=>$header['itemtype']));
                            $package['timelocked'] = true;
                            $header['nodestatus']= _COM_STATUS_NODE_LOCKED;
                        }
                    }
                }
            }
        }
    }

    if ($package['settings']['render'] == _COM_VIEW_THREADED) {
        $package['comments'] = comments_renderer_array_maptree($package['comments']);
    }
    $isanon= xarConfigGetVar('Site.User.AnonymousUID');
    // run text and title through transform hooks
    if (!empty($package['comments'])) {

        foreach ($package['comments'] as $key => $comment) {



            $comment['transformed-text'] = xarVarPrepHTMLDisplay($comment['xar_text']);
            $comment['transformed-title'] = xarVarPrepForDisplay($comment['xar_title']);
            // say which pieces of text (array keys) you want to be transformed
            $comment['transform'] = array('transformed-title','transformed-text');
            $comment['current_module'] = 'comments';
            $comment['current_itemtype'] = 0;
            // call the item transform hooks
            // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
            $package['comments'][$key] = xarModCallHooks('item', 'transform', $comment['xar_cid'], $comment, 'comments',xarModGetNameFromID($header['modid']), $header['itemtype']);

            //check for anon - better to get the data here than template
            $comment['anoninfo'] = '';
            if ($comment['xar_uid'] == $isanon && $comment['xar_postanon'] != '1' &&  $comment['xar_postanon'] != '0') {
                $temp = unserialize($comment['xar_postanon']);
                if (is_array($temp)) {
                   $comment['anoninfo'] = array('aname' =>isset($temp['name'])?$temp['name']:'', 'aemail'=>isset($temp['email'])?$temp['email']:'', 'aweb'=>isset($temp['web'])?$temp['web']:'');

                }
            }
            $package['comments'][$key]['anoninfo']= $comment['anoninfo'];

        }

    }

    $modinfo = xarModGetInfo($header['modid']);
    $header['lockurl']       =  '';
    $header['input-title']   = '';
    $header['lockurl_text']  = '';
    $header['lockurl_image']  = '';
    //set some messages based on root node

    if (!empty($package['comments']) || ($package['settings']['manuallock'] && $moderator) || $package['timelocked']) {
        if ($header['nodestatus']   == _COM_STATUS_NODE_LOCKED) {
                $header['input-title']  = xarML('Commenting is closed on this thread.');
                if ($package['settings']['manuallock'] && $moderator) {
                    $header['lockurl']      = xarModURL('comments','admin','setstatus',  array( 'cid' =>  $header['rootcid'], 'modid'=>$header['modid'],'itemtype' =>$header['itemtype'], 'status' => _COM_STATUS_ROOT_NODE,'returnurl'=>xarServerGetCurrentURL()));
                    $header['lockurl_text'] = xarML('Unlock Thread');
                      $header['lockurl_image'] = 'locked.gif';
                }
        } elseif ($header['nodestatus'] == _COM_STATUS_ROOT_NODE){
                $header['input-title']   = xarML('Post a new comment');
                if ($package['settings']['manuallock']  && $moderator) {
                    $header['lockurl']       = xarModURL('comments','admin','setstatus',  array( 'cid' => $header['rootcid'], 'modid'=>$header['modid'],'itemtype' =>$header['itemtype'],'status' => _COM_STATUS_NODE_LOCKED,'returnurl'=>xarServerGetCurrentURL()));
                    $header['lockurl_text'] = xarML('Lock Thread');
                    $header['lockurl_image'] = 'unlocked.gif';
                }
        }

    }

    $package['settings']['max_depth'] = _COM_MAX_DEPTH;
    $package['uid']                   = xarUserGetVar('uid');
    $package['uname']                 = xarUserGetVar('uname');
    $package['name']                  = xarUserGetVar('name');
    //for anonymous - let's initialise
    $package['aname'] = '';
    $package['aemail'] = '';
    $package['aweb'] = '';
    $package['isanon'] = $isanon;
    //strip tags on title - this could come from another module, already prepped
    $package['new_title']             = strip_tags(xarVarGetCached('Comments.title', 'title'));

    // Language strings for JS form validator
    $package['formcheckmsg'] = xarML('Please complete the following fields:');
    $package['titlelabel'] = xarML('Subject');
    $package['textlabel'] = xarML('Comment');

    // Let's honour the phpdoc entry at the top :-)
    if(isset($args['returnurl'])) {
        $receipt['returnurl']['raw'] = $args['returnurl'];
    }
    if (empty($ishooked) && empty($receipt['returnurl'])) {
        // get the title and link of the original object

        $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
            array('itemtype' => $header['itemtype'], 'itemids' => array($header['objectid'])),
            // don't throw an exception if this function doesn't exist
            0
        );

        if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
            $url = $itemlinks[$header['objectid']]['url'];
            if (!strstr($url, '?')) {
                $url .= '?';
            }
            $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
            $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
        } else {
            $url = xarModURL($modinfo['name'], 'user', 'main');
        }

        $receipt['returnurl'] = array('encoded' => rawurlencode($url), 'decoded' => $url);
    } elseif (!isset($receipt['returnurl']['raw'])) {
        if (empty($args['extrainfo'])) {
            $modinfo = xarModGetInfo($args['modid']);
            $receipt['returnurl']['raw'] = xarModURL($modinfo['name'],'user','main');
        } elseif (is_array($args['extrainfo']) && isset($args['extrainfo']['returnurl'])) {
            $receipt['returnurl']['raw'] = $args['extrainfo']['returnurl'];
        } elseif (is_string($args['extrainfo'])) {
            $receipt['returnurl']['raw'] = $args['extrainfo'];
        }
        if (!stristr($receipt['returnurl']['raw'], '?')) {
            $receipt['returnurl']['raw'] .= '?';
        }
        $receipt['returnurl']['decoded'] = $receipt['returnurl']['raw'] . $settings_uri;
        $receipt['returnurl']['encoded'] = rawurlencode($receipt['returnurl']['decoded']);
    } else {
        if (!stristr($receipt['returnurl']['raw'],'?')) {
            $receipt['returnurl']['raw'] .= '?';
        }
        $receipt['returnurl']['encoded'] = rawurlencode($receipt['returnurl']['raw']);
        $receipt['returnurl']['decoded'] = $receipt['returnurl']['raw'] . $settings_uri;
    }

    $receipt['post_url']              = xarModURL('comments', 'user', 'reply');
    $receipt['action']                = 'display';

    //notifications
    $currentuser = xarUserGetVar('uid');
    if ($package['settings']['usecommentnotify'] && xarUserIsLoggedIn()) {
        $donotify = xarModAPIFunc('comments','user','getnotifies',array('user_id'=>$currentuser,'module_id'=>$header['modid'],'object_id'=>$header['objectid'],'itemtype'=>$header['itemtype'],'notifytype'=>1));
        if (is_array($donotify)) {
           $package['isnotified'] = 1; //they are subscribed
        } else {
           $package['isnotified'] = 0;
        }
        $package['removenotify'] =xarModURL('comments','user','removenotify',array('user_id'=>$currentuser,'module_id'=>$header['modid'],'object_id'=>$header['objectid'],'itemtype'=>$header['itemtype'],'notifytype'=>1,'returnurl'=>xarServerGetCurrentURL()));
        $package['notifyme'] =  xarModURL('comments','user','setnotify',array('user_id'=>$currentuser,'module_id'=>$header['modid'],'object_id'=>$header['objectid'],'itemtype'=>$header['itemtype'],'notifytype'=>1,'returnurl'=>xarServerGetCurrentURL()));
    } else {
        $package['removenotify'] = '';
        $package['notifyme'] = '';
        $package['isnotified'] = 0;
    }

   $package['numberofcomments'] = is_array($package['comments']) ? count($package['comments']) : 0;
    /*  Generate a onetime authorisation code for this operation */
    $package['authid'] = xarSecGenAuthKey('comments');
    //$package['rssurl']=xarModURL('comments','user','rss',array('module_id'=>$header['modid'],'object_id'=>$header['objectid'],'itemtype'=>$header['itemtype']));

    $hooks = xarModAPIFunc('comments','user','formhooks');

    $header['quickpost'] = isset($quickpost)? $quickpost : 0;
    $package['moderated'] ='';

    $output['hooks']   = $hooks;
    $output['header']  = $header;
    $output['package'] = $package;
    $output['receipt'] = $receipt;

    if ($package['settings']['quickpost'] && ($header['quickpost'] ==1)) {
        xarTplSetPageTemplateName('module');
     return xarTplModule('comments','user','dopost',$output);
    }

    return $output;
}
?>
