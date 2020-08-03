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
 * Grab the highest 'right' value for the specified modid/objectid pair
 * @access   private
 * @param    integer     modid      The module that comment is attached to
 * @param    integer     objectid   The particular object within that module
 * @param    integer     itemtype   The itemtype of that object
 * @returns   integer   the highest 'right' value for the specified modid/objectid pair or zero if it couldn't find one
 */
function comments_userapi_get_object_maxright( $args )
{

    extract ($args);

    $exception = false;

    if (!isset($modid) || empty($modid)) {
        throw new EmptyParameterException('modid');
    }

    if (!isset($objectid) || empty($objectid)) {
        throw new EmptyParameterException('objectid');
    }

    if (empty($itemtype)) {
        $itemtype = 0;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];

    // grab the root node's id, left and right values
    // based on the objectid/modid pair
    $sql = "SELECT  MAX($ctable[right]) as max_right
              FROM  $xartable[comments]
             WHERE  $ctable[objectid] = '$objectid'
               AND  $ctable[itemtype] = $itemtype
               AND  $ctable[modid] = $modid";

    $result = $dbconn->Execute($sql);

    if (!$result)
        return;

    if (!$result->EOF) {
        $node = $result->GetRowAssoc(false);
    } else {
        $node['max_right'] = 0;
    }
    $result->Close();

    return $node['max_right'];
}

?>
