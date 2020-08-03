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
 * Grab the left and right values for a particular node
 * (aka comment) in the database
 *
 * @access   public
 * @param    integer     $cid     id of the comment to lookup
 * @returns  array an array containing the left and right values or an
 *           empty array if the comment_id specified doesn't exist
 */
function comments_userapi_get_node_lrvalues( $args )
{

    extract( $args );

    if (empty($cid)) {
        throw new EmptyParameterException('cid');
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    $sql = "SELECT  $ctable[left], $ctable[right]
              FROM  $xartable[comments]
             WHERE  $ctable[cid]=$cid";

    $result = $dbconn->Execute($sql);

    if(!$result)
        return;

    if (!$result->EOF) {
        $lrvalues = $result->GetRowAssoc(false);
    } else {
        $lrvalues = array();
    }

    $result->Close();

    return $lrvalues;
}

?>
