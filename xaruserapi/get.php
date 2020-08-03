<?php
/**
 * Comments module get one - consistent apis for hooks
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Wrapper for getting a single comment
 * Making it consistent name for hook calls
 * @access   public
 * @param    integer    $args['cid']       the id of a comment
 * @returns  array   an array containing the sole comment that was requested
                     or an empty array if no comment found
 */
function comments_userapi_get( $args )
{

    $commentlist = xarModAPIFunc('comments','user','get_one',$args);

    return $commentlist;
}

?>