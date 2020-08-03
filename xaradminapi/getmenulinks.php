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
 * utility function pass individual menu items to the main menu
 *
 * @return array containing the menulinks for the main menu items.
 */
function comments_adminapi_getmenulinks()
{
    $menulinks[] = Array('url'   => xarModURL('comments',
                                              'admin',
                                              'review'),
                         'title' => xarML('Review comments'),
                         'label' => xarML('Review comments'),
                         'active' => array('review')
                         );

    $menulinks[] = Array('url'   => xarModURL('comments',
                                              'admin',
                                              'stats'),
                         'title' => xarML('View comments per module statistics'),
                         'label' => xarML('View Statistics'),
                         'active'=>array('stats'));

    $menulinks[] = Array('url'   => xarModURL('comments',
                                              'admin',
                                              'managenotifies'),
                         'title' => xarML('Manage notifications'),
                         'label' => xarML('Manage notifications'),
                         'active'=> array('managenotifies'));
    $menulinks[] = Array('url'   => xarModURL('comments',
                                              'admin',
                                              'hooks'),
                         'title' => xarML('Configure hooks'),
                         'label' => xarML('Configure hooks'),
                         'active'=> array('hooks'));
    $menulinks[] = Array('url'   => xarModURL('comments',
                                              'admin',
                                              'modifyconfig'),
                         'title' => xarML('Modify the comments module configuration'),
                         'label' => xarML('Modify Config'),
                         'active'=>array('modifyconfig'));

    if (empty($menulinks)){
        $menulinks = '';
    }

    return $menulinks;
}
?>