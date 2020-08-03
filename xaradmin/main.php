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
 * Overview Menu
 */
function comments_admin_main()
{
    if(!xarSecurityCheck('Comments-Admin')) {
        return;
    }
    // we only really need to show the default view (stats in this case)
    xarResponseRedirect(xarModURL('comments', 'admin', 'stats'));
    // success
    return true;
}
?>
