<?php
/**
 * Review and moderate comments
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
function comments_admin_review($args)
{
    // Security Check
    if(!xarSecurityCheck('CommentThreadModerator')) return;

    if (!xarVarFetch('modid',       'id',   $modid,     null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('modname',     'str',   $modname,   'all',XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('itemtype',    'int',  $itemtype,  null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('order',       'str',  $order,    'DESC',XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('howmany',     'int',  $howmany,   0,XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('first',       'int',  $first,     1,XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('page',        'id',   $page,      0,XARVAR_GET_OR_POST)) {return;};
    if (!xarVarFetch('object_id',   'id',   $object_id, NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('module_id',    'id',   $module_id, NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('status',      'int',  $status,   0,XARVAR_GET_OR_POST)) {return;} //default to all
    if (!xarVarFetch('showdetail',  'checkbox',  $showdetail,    false,XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('checklist',  'array',  $checklist, NULL,XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('bulk',    'int',  $bulk,  null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('returnurl', 'str:0:254',$returnurl,xarServerGetCurrentURL(),XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('searchterm',  'str:3',  $searchterm,    '',XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('maxfind',     'int',   $maxfind,    20,XARVAR_NOT_REQUIRED)) {return;};

    //array for search results
    $output['searchresults'] = array();
    $output['menulinks'] = xarModAPIFunc('comments','admin','getmenulinks');
    $output['searchterm']=$searchterm;
    $output['maxfind'] = $maxfind;
    //do the search if there is some search term returned
     $output['searching'] = false;
    if (!empty($searchterm)) {

        $output['searching'] = true;
        $args = array(
            'title'          => "%".$searchterm."%",
            'text'           => "%".$searchterm."%",
            'status'         => $status,
            'return_comment' => true,
            'maxitems'       => $maxfind,
        );
         $output['searchresults'] = xarModAPIFunc('comments','user','search', $args);
    }

    $howmany = !isset($howmany) || empty($howmany) ? xarModGetVar('comments','numstats') : 20;

    //get all hooked modules to comments with actual comments
    $hookedmodules = xarModAPIFunc('comments','user','getmodules');

    if (isset($modid) && $modid !=0) {
        $modname = xarModGetNameFromID($modid);
    } else {
        $modname = 'all';
    }

    $output['modname'] = $modname;
    $output['modid'] = $modid;
    $modlist = array();
    $modlist['0'] = xarML('All');

    foreach ($hookedmodules as $hookedmodid=>$hookedmod) {
       if (xarModGetNameFromID($hookedmodid)) {
           $modlist[$hookedmodid] = ucfirst(xarModGetNameFromID($hookedmodid));
        }
    }

    //let's get to it if there is a bulk action
    if (is_array($checklist) && !empty($checklist) && isset($bulk)) {
        $checklist = array_keys($checklist);
        if ($bulk >0) { //we're not deleting

            foreach($checklist as $cid) {
                $setstatus = xarModAPIFunc('comments','admin','setstatus',array('cid'=>(int)$cid,'status'=>$bulk));
            }
        } elseif ($bulk = -1) {
            foreach($checklist as $cid) {
                $item = xarModAPIFunc('comments','user','get_one', array('cid'=>$cid));
                $item = current($item);
                $deleted = xarModAPIFunc('comments','admin','delete_node',
                          array('node' => $item['xar_cid'],
                                'pid'  => $item['xar_pid']));
             }
        }
    }

   if ($page !=0) {
        if ($page ==1) {
           $status =1;
        }elseif ($page ==2) {
           $status = 2;
        } elseif ($page ==3) {
            $status = 4;
        }
    }

    if (!$output['searching'] ) {
        $items = xarModAPIFunc('comments', 'user', 'get_multipleall',array('status'=>$status, 'howmany' => (int)$howmany, 'first'=>$first,'modarray'=>array($modname))) ;
    } else {
        $items = $output['searchresults'];

    }
    //for spam and not spam - the function should also interact with akismet
    $isanon = xarConfigGetVar('Site.User.AnonymousUID');
    foreach ($items as $k=>$item) {
        $itemtype = isset($item['xar_itemtype'])? $item['xar_itemtype'] :0;
        $items[$k]['delete_url']     = xarModURL('comments','user','delete',    array( 'receipt[action]'=>'confirm-delete','header[pid]'=>$item['xar_pid'], 'header[cid]' => $item['xar_cid'], 'header[objectid]'=>$item['xar_objectid'],'header[modid]'=>$item['xar_modid'], 'header[itemtype]' =>$itemtype,'receipt[returnurl][review]'=>$returnurl));
        $items[$k]['approve_url']    = xarModURL('comments','admin','setstatus',  array( 'cid' => $item['xar_cid'], 'modid'=>$item['xar_modid'],'itemtype' =>$itemtype, 'objectid'=>$item['xar_objectid'],'status' => _COM_STATUS_ON, 'page'=>$page));
        $items[$k]['unapprove_url']  = xarModURL('comments','admin','setstatus',  array( 'cid' => $item['xar_cid'], 'modid'=>$item['xar_modid'],'itemtype' =>$itemtype,'objectid'=>$item['xar_objectid'], 'status' => _COM_STATUS_OFF, 'page'=>$page));
        $items[$k]['spam_url']       = xarModURL('comments','admin','setstatus',  array( 'cid' => $item['xar_cid'], 'modid'=>$item['xar_modid'],'itemtype' =>$itemtype,'objectid'=>$item['xar_objectid'], 'status' => _COM_STATUS_SPAM, 'page'=>$page));
        $items[$k]['notspam_url']    = xarModURL('comments','admin','setstatus',  array( 'cid' => $item['xar_cid'], 'modid'=>$item['xar_modid'],'itemtype' =>$itemtype,'objectid'=>$item['xar_objectid'], 'status' => _COM_STATUS_HAM,  'page'=>$page)); //5 is purely a signifier of 'not spam'.. setting should dictate where it goes to 1, or 2
        $items[$k]['title']= isset($item['xar_subject']) ? xarVarPrepForDisplay($item['xar_subject']) : xarVarPrepForDisplay($item['xar_title']);
        $items[$k]['text']=  isset($item['xar_text']) ?xarVarPrepHTMLDisplay($item['xar_text']): '';
        if (!$output['searching'] ) {
            //transforms if necessary
            $items[$k]['transformed']= xarModCallHooks('item', 'transform', $item['xar_cid'], $item, 'comments');
        }
        if (xarSecurityCheck('CommentThreadRead',0,'Thread',$item['xar_modid'].":".$item['xar_itemtype'].":".$item['xar_objectid'])) {
           //
        } else {
          unset($items['commentlist'][$day][$key]);
        }
        //check for anon - better to get the data here than template
        $item['anoninfo'] = '';
        if (($item['xar_uid'] == $isanon) && ($item['xar_postanon'] != '1') &&  ($item['xar_postanon'] != '0')) {
            $temp = unserialize($item['xar_postanon']);
            if (is_array($temp)) {
               $item['anoninfo'] = array('aname' =>isset($temp['name'])?$temp['name']:'', 'aemail'=>isset($temp['email'])?$temp['email']:'', 'aweb'=>isset($temp['web'])?$temp['web']:'');
            }
            $items[$k]['anoninfo'] = $item['anoninfo'];
        }
        if ($output['searching']) {
            $item['xar_author'] = $item['xar_uid']; //as xar_uid is returned from the search, and xar_author is a name
        }

    }
 // Add pager
    $itemtotal = xarModAPIFunc('comments', 'user', 'get_multipleall',array('status'=>$status, 'howmany'=>-1)) ;

    //let's do a normal pager if there is no searching going on
    if (empty($searchterm)) {
        $totalitems = count($itemtotal);

        $output['pager'] = xarTplGetPager($first,
                            $totalitems,
                            xarModURL('comments', 'admin', 'review',
                                      array('first' => '%%',
                                            'object_id' => (int)$object_id,
                                            'itemtype' => (int)$itemtype,
                                            'modid' => (int)$modid,
                                            'status' => (int)$status)),
                            $howmany);
    } else {
       $output['pager'] = '';
    }

    $output['modlist'] = $modlist;
    //setup bulk action choices
    $output['bulk'] = array(
            '1'=> xarML('Unapprove'),
            '2'=> xarML('Approve'),
            '4'=> xarML('Spam'),
            '5'=> xarML('Not Spam'),
            '-1'=> xarML('Delete')
            );
    $output['searchstatus'] = $status;
    $output['page']= $page;
    $output['showdetail']=$showdetail;
    $output['items']             = $items;
    $output['itemcount'] = count($items);
    $output['delete_all_url']   = xarModURL('comments','admin', 'delete', array('dtype' => 'all'));
    $output['isanon'] = $isanon;
    return $output;

}
?>