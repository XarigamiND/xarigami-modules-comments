<?php
/**
 * Overview displays standard Overview page
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
 * Overview displays standard Overview page
 *
 * @returns array xarTplModule with $data containing template data
 * @return array containing the menulinks for the overview item on the main manu
 * @since 14 Oct 2005
 */
function comments_admin_overview()
{
   /* Security Check */
    if (!xarSecurityCheck('Comments-Admin',0)) return;

    $data=array();

    /* if there is a separate overview function return data to it
     * else just call the main function that usually displays the overview
     */
    $data['menulinks'] = xarModAPIFunc('comments','admin','getmenulinks');
    return xarTplModule('comments', 'admin', 'main', $data, 'main');
}

?>
