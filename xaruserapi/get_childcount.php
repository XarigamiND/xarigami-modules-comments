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
 * Get the number of approved children comments for a particular comment id
 *
 * @author mikespub
 * @access public
 * @param integer    $cid       the comment id that we want to get a count of children for
 * @returns integer  the number of child comments for the particular comment id,
 *                   or raise an exception and return false.
 */
function comments_userapi_get_childcount($cid)
{

    if ( !isset($cid) || empty($cid) ) {
        throw new EmptyParameterException('cid');
    }


    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];

    $nodelr = xarModAPIFunc('comments',
                            'user',
                            'get_node_lrvalues',
                             array('id' => $cid));

    $sql = "SELECT  COUNT($ctable[cid]) as numitems
              FROM  $xartable[comments]
             WHERE  $ctable[status]="._COM_STATUS_ON."
               AND  ($ctable[left] >= $nodelr[xar_left] AND $ctable[right] <= $nodelr[xar_right])";

    $result = $dbconn->Execute($sql);
    if (!$result)
        return;

    if ($result->EOF) {
        return 0;
    }

    list($numitems) = $result->fields;

    $result->Close();

    // return total count - 1 ... the -1 is so we don't count the comment root.
    return ($numitems - 1);
}

?>
