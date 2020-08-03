<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2007-2012 2skies.com
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Hooks shows the configuration of hooks for other modules
 *
 * @return array $data containing template data
 */
function comments_admin_hooks()
{
    // Security check
    if(!xarSecurityCheck('Comments-Read',0)) return;

    $data = array();

    return $data;
}

?>
