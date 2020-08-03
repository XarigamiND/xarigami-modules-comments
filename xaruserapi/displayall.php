<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Display comments from one or more modules and item types
 * @author jojodee - Moved to api function for use in Blocks
 * @return array of comment data
 */
function comments_userapi_displayall($args)
{
    if (!xarVarFetch('modid','array',$args['modid'],array('all'),XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('itemtype','int',$args['itemtype'],null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('order','str',$args['order'],'DESC',XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('howmany','id',$args['howmany'],20,XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('first','id',$args['first'],1,XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('status','int',$args['status'],0,XARVAR_GET_OR_POST)) {return;};//all comments status 0

    if (empty($args['truncate'])) {
        $args['truncate']='';
    }
    if (!isset($args['addmodule'])) {
        $args['addmodule']='off';
    }
    if (!isset($args['addobject'])) {
        $args['addobject']=21;
    }
    if (!isset($args['addcomment'])) {
        $args['addcomment']=20;
    }
    if (!isset($args['adddate'])) {
        $args['adddate']='on';
    }
    if (!isset($args['adddaysep'])) {
        $args['adddaysep']='on';
    }
    if (!isset($args['addauthor'])) {
        $args['addauthor']=1;
    }
    if (!isset($args['addprevious'])) {
        $args['addprevious']=0;
    }

    $args['returnurl'] = '';
    $modarray = $args['modid'];
    // get the list of modules+itemtypes that comments is hooked to
    $hookedmodules = xarModAPIFunc('modules', 'admin', 'gethookedmodules',
                                   array('hookModName' => 'comments'));

    // initialize list of module and pubtype names
    $modlist = array();
    $modname = array();
    $modview = array();
    $modlist['all'] = xarML('All');
    // make sure we only retrieve comments from hooked modules
    $todolist = array();
    if (isset($hookedmodules) && is_array($hookedmodules)) {
        foreach ($hookedmodules as $module => $value) {
            $modid = xarModGetIDFromName($module);
            if (!isset($modname[$modid])) $modname[$modid] = array();
            if (!isset($modview[$modid])) $modview[$modid] = array();
            $modname[$modid][0] = ucwords($module);
            $modview[$modid][0] = xarModURL($module,'user','view');
            // Get the list of all item types for this module (if any)
            $mytypes = xarModAPIFunc($module,'user','getitemtypes',
                                     // don't throw an exception if this function doesn't exist
                                     array(), 0);
            if (!empty($mytypes) && count($mytypes) > 0) {
                 foreach (array_keys($mytypes) as $itemtype) {
                     $modname[$modid][$itemtype] = $mytypes[$itemtype]['label'];
                     $modview[$modid][$itemtype] = $mytypes[$itemtype]['url'];
                 }
            }
            // we have hooks for individual item types here
            if (!isset($value[0])) {
                foreach ($value as $itemtype => $val) {
                    $todolist[] = "$module.$itemtype";
                    if (isset($mytypes[$itemtype])) {
                        $type = $mytypes[$itemtype]['label'];
                    } else {
                        $type = xarML('type #(1)',$itemtype);
                    }
                    $modlist["$module.$itemtype"] = ucwords($module) . ' - ' . $type;
                }
            } else {
                $todolist[] = $module;
                $modlist[$module] = ucwords($module);
                // allow selecting individual item types here too (if available)
                if (!empty($mytypes) && count($mytypes) > 0) {
                    foreach ($mytypes as $itemtype => $mytype) {
                        if (!isset($mytype['label'])) continue;
                        $modlist["$module.$itemtype"] = ucwords($module) . ' - ' . $mytype['label'];
                    }
                }
            }
        }
    }

    // replace 'all' with the list of hooked modules (+ xarbb if necessary ?)
    if (count($modarray) == 1 && $modarray[0] == 'all') {
        $args['modarray'] = $todolist;
    } else {
        $args['modarray'] = $modarray;
    }

    $comments = xarModAPIFunc('comments','user','get_multipleall',$args);
    $settings = xarModAPIFunc('comments','user','getoptions');

    if (!empty($args['order'])) {
        $settings['order']=$args['order'];
    }

    // get title and link for all module items (where possible)
    $items = array();
    if (!empty($args['addobject'])) {
        // build a list of item ids per module and item type
        foreach (array_keys($comments) as $i) {
            $modid = $comments[$i]['xar_modid'];
            $itemtype = $comments[$i]['xar_itemtype'];
            if (!isset($items[$modid])) $items[$modid] = array();
            if (!isset($items[$modid][$itemtype])) $items[$modid][$itemtype] = array();
            $itemid = $comments[$i]['xar_objectid'];
            $items[$modid][$itemtype][$itemid] = '';
        }
        // for each module and itemtype, retrieve the item links (if available)
        foreach ($items as $modid => $itemtypes) {
            $modinfo = xarModGetInfo($modid);
            foreach ($itemtypes as $itemtype => $itemlist) {
                $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                                           array('itemtype' => $itemtype,
                                                 'itemids' => array_keys($itemlist)),
                                           // don't throw an exception if this function doesn't exist
                                           0);
                if (!empty($itemlinks) && count($itemlinks) > 0) {
                    foreach ($itemlinks as $itemid => $itemlink) {
                        $items[$modid][$itemtype][$itemid] = $itemlink;
                    }
                }
            }
        }
    }

    // generate comments array of arrays; each date has an array of comments
    // posted on that date
    $commentsarray = array();
    $timenow = time();
    $hoursnow = xarLocaleFormatDate("%H",$timenow);
    $dateprev = '';
    $numcomments = count($comments);
    $isanon= xarConfigGetVar('Site.User.AnonymousUID');
    for ($i=0;$i<$numcomments;$i++) {

        if ($args['adddaysep']=='on') {
        // find out whether to change day separator
            $msgunixtime=$comments[$i]['xar_datetime'];
            $msgdate=xarLocaleFormatDate("%b %d, %Y",$msgunixtime);
            $msgday=xarLocaleFormatDate("%A",$msgunixtime);

            $hoursdiff=($timenow - $msgunixtime)/3600;
            if($hoursdiff<$hoursnow && $msgdate!=$dateprev) {
                $daylabel=xarML('Today');
            }
            elseif($hoursdiff>=$hoursnow && $hoursdiff<$hoursnow+24 && ($msgdate!=$dateprev) ) {
                $daylabel=xarML('Yesterday');
            }
            elseif($hoursdiff>=$hoursnow+24 && $hoursdiff<$hoursnow+48 && $msgdate!=$dateprev) {
                $daylabel=xarML('Two days ago');
            }
            elseif ($hoursdiff>=$hoursnow+48 && $hoursdiff<$hoursnow+144 && $msgdate!=$dateprev) {
                $daylabel=$msgday;
            }
            elseif ($hoursdiff>=$hoursnow+144 && $msgdate!=$dateprev) {
                $daylabel=$msgdate;
            }
            $dateprev=$msgdate;
        }  else {
        // no need to keep track of date
            $daylabel = 'none';
        }

        // add title, url and truncate comments if requested
        $modid = $comments[$i]['xar_modid'];
        $itemtype = $comments[$i]['xar_itemtype'];
        $itemid = $comments[$i]['xar_objectid'];
        if (!empty($args['addobject']) && !empty($items[$modid][$itemtype][$itemid])) {
            $comments[$i]['xar_title'] = $items[$modid][$itemtype][$itemid]['label'];
            $comments[$i]['objurl'] = $items[$modid][$itemtype][$itemid]['url'];
        }
        if (isset($modname[$modid][$itemtype])) $comments[$i]['modname']=$modname[$modid][$itemtype];
        if (isset($modview[$modid][$itemtype])) $comments[$i]['modview']=$modview[$modid][$itemtype];

        //$comments[$i]['returnurl'] = urlencode($modview[$modid][$itemtype]);
        $comments[$i]['returnurl'] = null;
        if ($args['truncate']) {
            if ( strlen($comments[$i]['xar_subject']) >$args['truncate']+3 )  {
                $comments[$i]['xar_subject']=substr($comments[$i]['xar_subject'],0,$args['truncate']).'...';
            }
            if ( !empty($comments[$i]['xar_title']) && strlen($comments[$i]['xar_title']) >$args['truncate']-3 ) {
                $comments[$i]['xar_title']=substr($comments[$i]['xar_title'],0,$args['truncate']).'...';
            }
        }
        $comments[$i]['xar_subject'] = xarVarPrepForDisplay($comments[$i]['xar_subject']);
        if (!empty($comments[$i]['xar_text'])) {
            $comments[$i]['xar_text'] = xarVarPrepHTMLDisplay($comments[$i]['xar_text']);
        }
        $comments[$i]['xar_subjectplain'] =  strip_tags($comments[$i]['xar_subject']);
        list($comments[$i]['xar_text'],
             $comments[$i]['xar_subject']) = xarModCallHooks('item',
                                                             'transform',
                                                              $comments[$i]['xar_cid'],
                                                             array($comments[$i]['xar_text'],
                                                                   $comments[$i]['xar_subject']),
                                                                   'comments');

        if ($comments[$i]['xar_postanon'] != '0' && $comments[$i]['xar_postanon'] != '1') {
            $anoninfoarray = unserialize($comments[$i]['xar_postanon']);
            if (is_array($anoninfoarray)) {
                $anoninfo = array('aname'=>$anoninfoarray['name'],'aemail'=>$anoninfoarray['email'],'aweb'=>$anoninfoarray['web']);
            }
        } else {
            //initialize so we dont' get unset var errors
            $anoninfo = array('aname'=>'','aemail'=>'','aweb'=>'');
        }
        $comments[$i]['anoninfo'] = $anoninfo;
        $commentsarray[$daylabel][] = $comments[$i];
    }
    $templateargs['settings'] = $settings;
    $templateargs['isanon'] = $isanon;
    // prepare for output
    $templateargs['order']          =$args['order'];
    $templateargs['howmany']        =$args['howmany'];
    $templateargs['first']          =$args['first'];
    $templateargs['adddate']        =$args['adddate'];
    $templateargs['adddaysep']      =$args['adddaysep'];
    $templateargs['addauthor']      =$args['addauthor'];
    $templateargs['addmodule']      =$args['addmodule'];
    $templateargs['addcomment']     =$args['addcomment'];
    $templateargs['addobject']      =$args['addobject'];
    $templateargs['addprevious']    =$args['addprevious'];
    $templateargs['modarray']       =$modarray;
    $templateargs['modid']          =$modarray;
    $templateargs['itemtype']       =isset($itemtype)?$itemtype:0;
    $templateargs['modlist']        =$modlist;
    $templateargs['decoded_returnurl'] = rawurldecode(xarModURL('comments','user','displayall'));
    $templateargs['decoded_nexturl'] = xarModURL('comments','user','displayall',array(
                                                                         'first'=>$args['first']+$args['howmany'],
                                                                            'howmany'=>$args['howmany'],
                                                                            'modid'=>$modarray)
                                                            );
    $templateargs['commentlist']    =$commentsarray;
    $templateargs['order']          =$settings['order'];


   $templateargs['olderurl']=xarModURL('comments','user','displayall',
                                            array(
                                                'first'=>   $args['first']+$args['howmany'],
                                                'howmany'=> $args['howmany'],
                                                'modid'=>$modarray
                                                )
                                            );

    return $templateargs;
}
?>
