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
 * DeActivate the specified comment
 *
 * @access   public
 * @param    integer     $cid     id of the comment to lookup
 * @returns  bool      returns true on success, throws an exception and returns false otherwise
 */
function comments_userapi_deactivate( $args )
{
    extract($args);

    if (empty($cid)) {
        throw new EmptyParameterException('cid');
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    // First grab the objectid and the modid so we can
    // then find the root node.
    $sql = "UPDATE $xartable[comments]
            SET xar_status='"._COM_STATUS_OFF."'
            WHERE xar_cid=?";
    $bindvars = array((int) $cid);

    $result = $dbconn->Execute($sql,$bindvars);

    if (!$result)
        return;
}

?>
