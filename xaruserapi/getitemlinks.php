<?php
/**
 * Comments module - Allows users to post comments on items
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
 * utility function to pass individual item links to whoever
 *
 * @param $args['itemtype'] item type (optional)
 * @param $args['itemids'] array of item ids to get
 * @returns array
 * @return array containing the itemlink(s) for the item(s).
 */
function comments_userapi_getitemlinks($args)
{
    extract($args);
    $itemlinks = array();
    if (!xarSecurityCheck('Comments-Read', 0)) {
        return $itemlinks;
    }
    if (empty($itemids)) {
        $itemids = array();
    }
// FIXME: support retrieving several comments at once
    foreach ($itemids as $itemid) {
        $item = xarModAPIFunc('comments', 'user', 'get_one', array('cid' => $itemid));
        if (!isset($item)) return;
        if (!empty($item) && !empty($item[0]['xar_title'])) {
            $title = $item[0]['xar_title'];
        } else {
            $title = xarML('Comment #(1)',$itemid);
        }
        $itemlinks[$itemid] = array('url'   => xarModURL('comments', 'user', 'display',
                                                         array('cid' => $itemid)),
                                    'title' => xarML('Display Comment'),
                                    'label' => xarVarPrepForDisplay($title));
    }
    return $itemlinks;
}
?>
