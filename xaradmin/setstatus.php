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
 sys::import('modules.comments.xarincludes.defines');
function comments_admin_setstatus($args)
{

    if (!xarVarFetch('itemid',    'int',      $itemid,   null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('cid',       'int',      $cid,   null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('status',    'int',      $status,null,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('page',      'int',      $page,  0,XARVAR_NOT_REQUIRED)) {return;};
    if (!xarVarFetch('returnurl', 'str:0:254',$returnurl,'',XARVAR_NOT_REQUIRED)) {return;};
    if(!xarVarFetch('modid',       'id',   $modid,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('itemtype',   'id',   $itemtype,   NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('objectid',    'int',      $objectid,   null,XARVAR_NOT_REQUIRED)) {return;};

    // Security Check
    if (isset($objectid) && !empty($objectid)) {
        if(!xarSecurityCheck('CommentThreadModerator',0,'',"{$modid}:{$itemtype}:{$objectid}")) return;
    } else {
        if(!xarSecurityCheck('CommentThreadModerator',0,'',"{$modid}:{$itemtype}:All")) return;
    }
    //maybe this is sending in itemid via a hook, we need to check
    if (!isset($cid) || empty($cid)) {
        $cid = isset($itemid) ? $itemid :0;
    }
    if ((empty($cid)) || (empty($status)) || empty($modid)) {
        $msg = xarML('No cid or status or modid was provided - these must be provided to set a status');
        throw new EmptyParameterException(null,$msg);
    }

    $setstatus = xarModAPIFunc('comments','admin','setstatus',array('cid'=>(int)$cid,'status'=>(int)$status, 'modid'=>$modid,'itemtype'=>$itemtype));
    if ($status ==_COM_STATUS_OFF|| $status == _COM_STATUS_ON || $status == _COM_STATUS_SPAM || $status == _COM_STATUS_HAM) {
        if ($status == _COM_STATUS_OFF) {
            $statmsg = xarML('unapproved');
        }elseif ($status ==_COM_STATUS_ON) {
            $statmsg = xarML('approved');
        }elseif ($status == _COM_STATUS_SPAM) {
             $statmsg = xarML('spam');
        } elseif ($status == _COM_STATUS_HAM) {
            $statmsg = xarML('not spam');
        }
        $msg = xarML('Comment status was successfully set to #(1).',$statmsg);
        xarTplSetMessage($msg,'status');
    }
    if (!empty($returnurl)) {
        xarResponseRedirect($returnurl);
    } elseif (!empty($page) ) {
        xarResponseRedirect(xarModURL('comments', 'admin', 'review', array('page' => $page,'modid'=>$modid)));
    } else {
        xarResponseRedirect(xarModURL('comments', 'admin', 'review',array('modid'=>$modid)));
    }

}
?>