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
 * Called from the core when a module is removed.
 *
 * Delete the appertain comments when the module is hooked.
 */
function comments_adminapi_remove_module( $args )
{
    extract($args);

    // When called via hooks, we should get the real module name from objectid
    // here, because the current module is probably going to be 'modules' !!!
    if (!isset($objectid) || !is_string($objectid)) {
        throw new EmptyParameterException('objectid');
    }

    $modid = xarModGetIDFromName($objectid);
    if (empty($modid)) {
        throw new EmptyParameterException('modid');
    }

    // TODO: re-evaluate this for hook calls !!
    // Security check - important to do this as early on as possible to
    // avoid potential security holes or just too much wasted processing
    // if(!xarSecurityCheck('DeleteHitcountItem',1,'Item',"All:All:$objectid")) return;

// FIXME: we need to remove the comments for items of all types here, so a direct DB call
//        would be better than this "delete recursively" trick
    xarModAPIFunc('comments','admin','delete_module_nodes',array('modid'=>$modid));
    return $extrainfo;

}
?>