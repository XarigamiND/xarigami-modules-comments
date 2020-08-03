<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Set comment status
 * @access private
 * @returns mixed description of return
 */
sys::import('modules.comments.xarincludes.defines');
function comments_adminapi_setstatus($args)
{
    extract($args);
    $settings = xarModAPIFunc('comments','user','getoptions',$args);

    if (!is_numeric($cid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'CID', 'admin', 'setstatus',
                    'Comments');
        throw new BadParameterException(null,$msg);
    } elseif (!is_numeric($status)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'status', 'admin', 'setstatus',
                    'Comments');
        throw new BadParameterException(null,$msg);
    }

    if (!isset($modid)) $modid = xarModGetIDFromName('comments');
    //let's keep the original status for hooks that might need it
    $originalstatus = $status;

    if ($status == _COM_STATUS_HAM)  { //we are marking as not spam - do it here as we have modid
        //we set to approve or disapprove based on mod vars

        $module = xarModGetNameFromID($modid);
        if (isset($itemtype)) {
            $requireapproval = xarModGetVar($module,'requireapproval.'.$itemtype);
        } else {
            $requireapproval = xarModGetVar($module,'requireapproval');
        }
        $status = $requireapproval ? _COM_STATUS_OFF : _COM_STATUS_ON;  //if require approval - unapproved, otherwise approved

    }
     //get the comment first - ensure it exists
    $comment = xarModAPIFunc('comments','user','get_one', array('cid'=>$cid));
    if (!is_array($comment)) return;
    $comment = current($comment);
    $anonuid = xarConfigGetVar('Site.User.AnonymousUID');
    $author = $comment['xar_uid'];

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $commenttable = $xartable['comments'] ;
    $bindvars = array();

    $query =  "UPDATE $commenttable
                SET xar_status    = ?
                WHERE xar_cid = ?";
    $bindvars = array($status,$cid);
    $result = $dbconn->Execute($query,$bindvars);

    if (!$result) return;
    //what about notifications?
    if ($settings['usecommentnotify'] && ($originalstatus != _COM_STATUS_ON) && ($status ==_COM_STATUS_ON)) {
       //We have a comment  now approved
       if (isset($pid)) { //parent thread
         $donotifiesreplies = xarModAPIFunc('comments','user','donotify',array('author'=>$author,'parent_id'=>$pid,'itemtype'=>$itemtype,'object_id'=>$objectid,'module_id'=>$modid,'cid'=>$id,'notifytype'=>_COM_NOTIFY_REPLY));
       }
        $donotifiesthread = xarModAPIFunc('comments','user','donotify',array('author'=>$author,'parent_id'=>0,'itemtype'=>$itemtype,'object_id'=>$objectid,'module_id'=>$modid,'cid'=>$id,'notifytype'=>_COM_NOTIFY_THREAD));
    }

    // Call update hooks for categories etc.
    $args['module'] = 'comments';
    $args['itemtype'] = 0;
    $args['itemid'] = $cid;
    $args['status'] = $originalstatus;
    xarModCallHooks('item', 'update', $cid, $args);

    return true;
}

?>