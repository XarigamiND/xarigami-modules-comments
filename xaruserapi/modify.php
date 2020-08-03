<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2009 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Modify a comment
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 * @returns mixed description of return
 */
function comments_userapi_modify($args)
{

    extract($args);

    $msg = xarML('Missing or Invalid Parameters: ');
    $error = FALSE;

    if (!isset($title)) {
        $msg .= xarMLbykey('title ');
        $error = TRUE;
    }

    if (!isset($cid)) {
        $msg .= xarMLbykey('cid ');
        $error = TRUE;
    }

    if (!isset($text)) {
        $msg .= xarMLbykey('text ');
        $error = TRUE;
    }

    if (!isset($postanon)) {
        $msg .= xarMLbykey('postanon ');
        $error = TRUE;
    }
    if(isset($itemtype) && !xarVarValidate('int:0:', $itemtype)) {
            $msg .= xarML('itemtype');
            $error = TRUE;
    }

    if(isset($objectid) && !xarVarValidate('int:1:', $objectid)) {
            $msg .= xarML('objectid');
            $error = TRUE;
    }

    if(isset($date) && !xarVarValidate('int:1:', $date)) {
            $msg .= xarML('date');
            $error = TRUE;
    }

    if(isset($status) && !xarVarValidate('enum:1:2:3:4', $status)) {
            $msg .= xarML('status');
            $error = TRUE;
    }

    if(!isset($editstamp)) {
        $editstamp = 0;
    }
    if ($error) {
        throw new BadParameterException('error');

    }

    $hostname = xarSessionGetIPAddress();


    $adminid = xarModGetVar('roles','admin');

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    // Let's leave a link for the changelog module if it is hooked to track comments
    // jojodee: good idea. I'll move it direct to comments template and then can add it to
    //            any others we like as well, like xarbb.

    if  (($editstamp ==1 ) ||
                     (($editstamp == 2 ) && (xarUserGetVar('uid')<>$adminid))) {
        //jojo - was going to strip the html comments so they don't show up when editting a post
        // but it will strip out other content such as someone posting code with comments in it

        $text .= "\n";
        $text .= xarTplModule('comments','user','modifiedby', array(
                              'isauthor' => (xarUserGetVar('uid') == $authorid),
                              'postanon'=>$postanon));
        $text .= "\n"; //let's keep the begin and end tags together around the wrapped content

    }

    $sql =  "UPDATE $xartable[comments]
                SET xar_title    = ?,
                    xar_text     = ?,
                    xar_anonpost = ?";
    $bpostanon = empty($postanon) ? 0 : 1;
    $bindvars = array($title, $text, $bpostanon);

    if(isset($itemtype)) {
        $sql .= ",\nxar_itemtype = ?";
        $bindvars[] = $itemtype;
    }

    if(isset($objectid)) {
        $sql .= ",\nxar_objectid = ?";
        $bindvars[] = $objectid;
    }

    if(isset($date)) {
        $sql .= ",\nxar_date = ?";
        $bindvars[] = $date;
    }

    if(isset($status)) {
        $sql .= ",\nxar_status = ?";
        $bindvars[] = $status;
    }

    $sql .= "\nWHERE xar_cid = ?";
    $bindvars[] = $cid;

    $result = $dbconn->Execute($sql,$bindvars);

    if (!$result) {
                $msg = xarML('There was an error and the comment could not be modified.');
            xarTplSetMessage($msg,'error');
            return;

    }
    $msg = xarML('The comment was successfully modified.');
    xarTplSetMessage($msg,'status');
    // Call update hooks for categories etc.
    $args['module'] = 'comments';
    $args['itemtype'] = 0;
    $args['itemid'] = $cid;
    $args['text']   = $text;
    $args['title']  = $title;

    xarModCallHooks('item', 'update', $cid, $args);

    return true;
}

?>