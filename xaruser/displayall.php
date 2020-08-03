<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Display comments from one or more modules and item types
 * DEPRECATED - moved to user api, Please use the userapi function
 * Function to be removed in version 1.5.0 - instead at 1.6.0 calls the new api function 
 *
 */
function comments_user_displayall($args)
{
    $commentinfo = xarModAPIFunc('comments','user','displayall',$args);
     if (empty($args['block_is_calling'])) {
        $args['block_is_calling']=0;
    }
  
    if ($args['block_is_calling']==0 )   {
        $output=xarTplModule('comments', 'user','displayall', $commentinfo);
    } else {
        $templateargs['olderurl']=xarModURL('comments','user','displayall',
                                            array(
                                                'first'=>   $args['first']+$args['howmany'],
                                                'howmany'=> $args['howmany'],
                                                'modid'=>$modarray
                                                )
                                            );
                                            return $templateargs;

    }
    return $output;
}
?>
