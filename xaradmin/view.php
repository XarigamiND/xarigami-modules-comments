<?php
/**
 * contains the module information
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * This is a standard function to modify the configuration parameters of the
 * module
 * @return array
 */
function comments_admin_view()
{
    // Security Check
    if(!xarSecurityCheck('Comments-Admin')) {
        return;
    }

    return array();
}
?>