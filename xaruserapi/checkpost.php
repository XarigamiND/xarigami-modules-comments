<?php
/**
 * Check a post for publishing
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Check a post to see if it passes moderation requirements
 *
 * @param    integer     $args['modid']      the module id
 * @param    integer     $args['itemtype']   the item type
 * @param    string      $args['objectid']   the item id
 * @param    integer     $args['pid']        the parent id
 * @param    string      $args['title']      the title (title) of the comment
 * @param    string      $args['comment']    the text (body) of the comment
 * @param    integer     $args['postanon']   whether or not this post is gonna be anonymous
 * @param    integer     $args['author']     user id of the author
 * @param    string      $args['hostname']   hostname
 * @param    datetime    $args['date']       date of the comment
 * @param    integer     $args['cid']        comment id
 * @param str    $args['returnurl']
 * @return int   status for comment
 *
 */

function comments_userapi_checkpost($args)
{
    extract($args);

    //initialize - assume the status is moderated to start with as this is safest
    $status = _COM_STATUS_OFF;

    if (!isset($user_id)) {
       $user_id = xarUserGetVar('uid');
    }
    $settings = xarModAPIFunc('comments','user','getoptions',$args);

    //Do the tests in order so that the test with highest priorty is last
    // or the test that are absolute can be done first and we can return quickly

    // 1. Global - Hold post if it has links (number -' moderatelinknum'), 'moderatelinks'
    if (xarModGetVar('comments','moderatelinks') ==1) {

        $maxlinknum = xarModGetVar('comments','moderatelinknum');
        $htmlcount = 0;
        $bbcodecount = 0;
        $teststring = $title.' '.$comment;

        //test for a html link
        $htmllinkcheck = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if(preg_match_all("/$htmllinkcheck/siU", $teststring, $matches, PREG_SET_ORDER)) {
            $htmlcount = count($matches);
        }
        //test for a bbcode link
        $bbcodecheck = "\[url([^\[]*)\[\/url\]";
        if(preg_match_all("/$bbcodecheck/siU", $teststring, $matches, PREG_SET_ORDER)) {
            $bbcodecount = count($matches);
        }
        //let's add them in case they used both
        $totallinks = $htmlcount + $bbcodecount;
        if ($totallinks >= $maxlinknum) {
            $status =  _COM_STATUS_SPAM;
            //get out of here as it's spam
            return $status;
        }

    }

    // 2. Global - If any of the following words or IP are associated with the post. One word or IP per line.'moderatewords'
    //We want to match the host against the list, and match each word against
    $moderatedwords = xarModGetVar('comments','moderatewords');

    if (!empty($moderatedwords)) {
        $moderatedarray = explode(';',$moderatedwords);
        foreach($moderatedarray as $k=>$word) { //test for IP/host first
            $test = strstr(trim($word),$hostname);
            if ($test)  {
                $status =  _COM_STATUS_OFF;
                return $status; //that's it - get out of here
            }
        }
        //now test each word against the text and title
        $teststring = $title.' '.$comment;
        foreach($moderatedarray as $k=>$word) {
            $test = strstr($teststring,trim($word));
            if ($test) {
                $status =  _COM_STATUS_OFF;
                return $status; //that's it - get out of here
            }
        }

    }
    // 3. Global - If any of the following words or IP are associated with the post. One word or IP per line. 'blacklistwords'
    $blacklistwords = xarModGetVar('comments','blacklistwords');

    if (!empty($blacklistwords)) {
        $blacklistarray = explode(';',$blacklistwords);

        foreach($blacklistarray as $k=>$word) { //test for IP/host first
            $test = strstr(trim($word),$hostname);
            if ($test)  {
                $status =  _COM_STATUS_SPAM;
                return $status; //that's it - get out of here
            }
        }
        //now test each word against the text and title
        $teststring = $title.' '.$comment;
        foreach($blacklistarray as $k=>$word) {
            $test = strstr($teststring,trim($word));
            if ($test) {
                $status =  _COM_STATUS_SPAM;

                return $status; //that's it - get out of here
            }
        }
    }

    // 4. Itemtype - Do comments need approval before authorization?  'approvalrequired'
    if ($settings['approvalrequired'] == 1) {
        $status =  _COM_STATUS_OFF;
    } else {
        $status =  _COM_STATUS_ON;
    }

    // 5. Itemtype - Author has at least one prior approved comment? 'approvalifprevious'
    //get the comments by this author for this module (or itemtype??)
    //If approval required - does this override it?
    if (($settings['approvalifprevious'] == 1) && ($author != _XAR_ID_UNREGISTERED)) { //we need to check if they have posted, as long as they are not anonymous users
        if ($status != _COM_STATUS_ON) { //only proceed if we need to see if we turn it on
            $authorcount = xarModAPIFunc('comments','user','get_author_count',
                                            array('modid'   => $modid,
                                                  'author'  => $author,
                                                  'status'  => _COM_STATUS_ON    //must be approved
                                                 )
                                    );
            if (isset($authorcount) && $authorcount > 0) {
                $status =  _COM_STATUS_ON;
               return $status; //that's it - get out of here
            }
        }
    }

    // 6. Itemtype - Anonymous must supply name or email?' approvalifinfo'
    //TODO we need to setup this anon name/email

     if (($settings['approvalifinfo'] == 1) && ($author == _XAR_ID_UNREGISTERED)) { //only if anon not logged in
        $anoninfo = !is_int($postanon)?unserialize($postanon):$postanon;
        if (is_array($anoninfo) && ($status !=  _COM_STATUS_OFF)) {//only proceed if we need to see if we turn it on
            $name = isset($anoninfo['name'])?$anoninfo['name']:'';
            $email = isset($anoninfo['email'])?$anoninfo['email']:'';
            $isname= xarVarValidate('str:2:',$name,true);
            $isemail= xarVarValidate('email',$email,true);
            if ($isname && $isemail) {
                $status =  _COM_STATUS_ON;
            }
        }
     }
    // 7. Itemtype - Anonymous posts are all held for approval no matter what
    if (($settings['holdallanon'] == 1) && ($author == _XAR_ID_UNREGISTERED)) { //only if anon not logged in

          $status =  _COM_STATUS_OFF;
     }
    return $status;
}

?>