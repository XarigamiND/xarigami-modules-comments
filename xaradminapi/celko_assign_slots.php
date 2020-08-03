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
 *  Recurse through an array and reassign the celko based
 *  left and right values for each node
 *
 *  @author Carl P. Corliss
 *  @access private
 *  @param  array   $data  The array containing all the data nodes to adjust
 *  @returns array  the modified array is passed back, or zero if it is empty
 */

function comments_adminapi_celko_assign_slots( $data )
{

    static $total = 0;
    static $depth = 0;

    if (!is_array($data)) {
        return 0;
    }

    foreach ($data as $node_id => $node_data) {
        $node_data['xar_depth'] = $depth++;
        $node_data['xar_left']  = $total++;
    if (isset($node_data['children'])) {
            $node_data['children'] = xarModAPIFunc('comments',
                                                   'admin',
                                                   'celko_assign_slots',
                                                    $node_data['children']);
        } else {
            $node_data['children'] = FALSE;
        }
        $node_data['xar_right'] = $total++;
        $depth--;
        $tree[$node_id] = $node_data;
    }

    return $tree;
}

?>
