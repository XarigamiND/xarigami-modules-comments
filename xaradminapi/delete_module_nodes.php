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
 * Delete all comments attached to the specified module id

 * @access  private
 * @param   integer     $modid      the id of the module that the comments are associated with
 * @param   integer     $itemtype   the item type that the comments are associated with
 * @returns bool true on success, false otherwise
 */
function comments_adminapi_delete_module_nodes( $args )
{
    extract($args);

    if (!isset($modid) || empty($modid)) {
        $msg = xarML('Missing or Invalid parameter \'modid\'!!');
        throw new EmptyParameterException(null,$msg);
    }
    if (!isset($itemtype)) {
        $itemtype = 0;
    }

    $return_value = TRUE;

    $pages = xarModAPIFunc('comments','user','get_object_list',
                            array('modid' => $modid,
                                  'itemtype' => $itemtype ));

    if (count($pages) <= 0 || empty($pages)) {
        return $return_value;
    } else {
        foreach ($pages as $object) {
            xarModAPIFunc('comments','admin','delete_object_nodes',
                          array('modid'     => $modid,
                                'itemtype'  => $itemtype,
                                'objectid'  => $object['pageid']));
        }
    }
    return $return_value;
}
?>