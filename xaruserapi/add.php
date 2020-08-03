<?php
/**
 * Comments module - Allows users to post comments on items
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
 * Adds a comment to the database based on the objectid/modid pair
 *
 * @author   Carl P. Corliss (aka rabbitt)
 * @access   public
 * @param    integer     $args['modid']      the module id
 * @param    integer     $args['itemtype']   the item type
 * @param    string      $args['objectid']   the item id
 * @param    integer     $args['pid']        the parent id
 * @param    string      $args['title']    the title (title) of the comment
 * @param    string      $args['comment']    the text (body) of the comment
 * @param    integer     $args['postanon']   whether or not this post is gonna be anonymous 0 or 1
 *           <jojodee> we will change this to hold the name/email/wwww in serialized array of an anon post, but still 0 or 1 for logged in users
 * @param    integer     $args['author']     user id of the author (for API access)
 * @param    string      $args['hostname']   hostname (for API access)
 * @param    datetime    $args['date']       date of the comment (for API access)
 * @param    integer     $args['cid']        comment id (for API access - import only)
 * @returns  integer     the id of the new comment
 */
function comments_userapi_add($args)
{
    $settings = xarModAPIFunc('comments','user','getoptions',$args);
    extract($args);

    if (!isset($modid) || empty($modid)) {
        throw new EmptyParameterException('modid');
    }
    $itemtype = isset($itemtype) ? (int)$itemtype :0;


    if (!isset($objectid) || empty($objectid)) {
        throw new EmptyParameterException('objectid');
    }

    if (!isset($pid) || empty($pid)) {
        $pid = 0;
    }

    if (!isset($title) || empty($title)) {
        throw new EmptyParameterException('title');
    }

    if (!isset($comment) || empty($comment)) {
       throw new EmptyParameterException('comment');
    }

    if (!isset($postanon) || empty($postanon)) {
        $postanon = 0;
    }

    if (!isset($author)) {
        $author = xarUserGetVar('uid');
    }

    if (!isset($hostname)) {
        $forwarded = xarSessionGetIpAddress();
        $hostname = preg_replace('/,.*/', '', $forwarded);
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    // parentid == zero then we need to find the root nodes
    // left and right values cuz we're adding the new comment
    // as a top level comment
    if ($pid == 0) {
        $root_lnr = xarModAPIFunc('comments','user','get_node_root',
                                   array('modid' => $modid,
                                         'objectid' => $objectid,
                                         'itemtype' => $itemtype));

        // ok, if the there was no root left and right values then
        // that means this is the first comment for this particular
        // modid/objectid combo -- so we need to create a dummy (root)
        // comment from which every other comment will branch from
        if (!count($root_lnr)) {
            $pid = xarModAPIFunc('comments','user','add_rootnode',
                                  array('modid'    => $modid,
                                        'objectid' => $objectid,
                                        'itemtype' => $itemtype));
        } else {
            $pid = $root_lnr['xar_cid'];
        }
    }

    // pid should now always have a value
    assert($pid!=0 && !empty($pid));

    // grab the left and right values from the parent
    $parent_lnr = xarModAPIFunc('comments',
                                'user',
                                'get_node_lrvalues',
                                 array('cid' => $pid));

    // there should be -at-least- one affected row -- if not
    // then raise an exception. btw, at the very least,
    // the 'right' value of the parent node would have been affected.
    if (!xarModAPIFunc('comments', 'user', 'create_gap',
                        array('startpoint' => $parent_lnr['xar_right'],
                              'modid'      => $modid,
                              'objectid'   => $objectid,
                              'itemtype'   => $itemtype))) {

            $msg  = xarML('Unable to create gap in tree for comment insertion! Comments table has possibly been corrupted.');
            $msg .= xarML('Please seek help on the developer forum at http://xarigami.com/forums');
            throw new DataNotFoundException(null,$msg); //jojo - perhaps this should be raised at db create_gap function with appropriate db error
    }

    $cdate    = time();
    $left     = $parent_lnr['xar_right'];
    $right    = $left + 1;
    //$status = xarModGetVar('comments','AuthorizeComments') ? _COM_STATUS_OFF : _COM_STATUS_ON;
     $whatstatus   = xarModAPIFunc('comments','user','checkpost',
                            array('modid'    => $modid,
                                  'objectid' => $objectid,
                                  'itemtype' => $itemtype,
                                  'pid'      => $pid,
                                  'title'    => $title,
                                  'comment'  => $comment,
                                  'postanon' => $postanon,
                                  'author'   => $author,
                                  'hostname' => $hostname,
                                 )
                            );

    if (isset($whatstatus) && is_int($whatstatus)) {
        $status = $whatstatus;
    } else {
        //fallback in case something is wrong to global
        $status = xarModGetVar('comments','approvalrequired') ?  _COM_STATUS_OFF : _COM_STATUS_ON;
    }

    if (!isset($id)) {
        $id = $dbconn->GenId($xartable['comments']);
    }

    $sql = "INSERT INTO $xartable[comments]
                (xar_cid,
                 xar_modid,
                 xar_itemtype,
                 xar_objectid,
                 xar_author,
                 xar_title,
                 xar_date,
                 xar_hostname,
                 xar_text,
                 xar_left,
                 xar_right,
                 xar_pid,
                 xar_status,
                 xar_anonpost)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $bdate = (isset($date)) ? $date : $cdate;
    $bpostanon = (empty($postanon)) ? 0 : $postanon;
    $bindvars = array($id, $modid, $itemtype, $objectid, $author, $title, $bdate, $hostname, $comment, $left, $right, $pid, $status, $bpostanon);

    $result = $dbconn->Execute($sql,$bindvars);

    if (!$result) {
        return;
    } else {
        $id = $dbconn->PO_Insert_ID($xartable['comments'], 'xar_cid');
        // CHECKME: find some cleaner way to update the page cache if necessary
        if (function_exists('xarOutputFlushCached') &&
            xarModGetVar('xarcachemanager','FlushOnNewComment')) {
            $modinfo = xarModGetInfo($modid);
            xarOutputFlushCached("$modinfo[name]-");
            xarOutputFlushCached("comments-block");
        }
        // Call create hooks for categories, hitcount etc.
        $args['module'] = 'comments';
        $args['itemtype'] = 0;
        $args['itemid'] = $id;

        // pass along the current module & itemtype for pubsub (urgh)
// FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
        $args['cid'] = 0; // dummy category
        $modinfo = xarModGetInfo($modid);
        $args['current_module'] = $modinfo['name'];
        $args['current_itemtype'] = $itemtype;
        $args['author'] = $author;
        $args['text'] = $comment;
        $args['title'] = $title;
        xarModCallHooks('item', 'create', $id, $args);


        //notifies - the function handles problem sends with a log file
        //do them one at a time so we can specify type of notify and later add futher user options for either here
        //Now we have moderation - should only notify when comment is approved status
        if ($settings['usecommentnotify'] && ($status ==_COM_STATUS_ON)) {
           //first reply notifies
            $donotifiesreplies = xarModAPIFunc('comments','user','donotify',array('author'=>$author,'parent_id'=>$pid,'itemtype'=>$itemtype,'object_id'=>$objectid,'module_id'=>$modid,'cid'=>$id,'notifytype'=>2));
            $donotifiesthread = xarModAPIFunc('comments','user','donotify',array('author'=>$author,'parent_id'=>0,'itemtype'=>$itemtype,'object_id'=>$objectid,'module_id'=>$modid,'cid'=>$id,'notifytype'=>1));
        }
        if ($status ==  _COM_STATUS_OFF) {
            xarSessionSetVar('comments.held',$id);
        }
        return (int)$id;
    }
}
?>