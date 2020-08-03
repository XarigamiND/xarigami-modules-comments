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
 * Acquire a list of objectid's associated with a
 * particular Module ID in the comments table
 *
 * @access  private
 * @param   integer     $modid      the id of the module that the objectids are associated with
 * @param   integer     $itemtype   the item type that these nodes belong to
 * @returns array       A list of objectid's
 */
function comments_userapi_get_object_list( $args )
{
    extract($args);

    if (!isset($modid) || empty($modid)) {
       throw new EmptyParameterException('modid');
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ctable = $xartable['comments_column'];

    $sql     = "SELECT DISTINCT $ctable[objectid] AS pageid
                           FROM $xartable[comments]
                          WHERE $ctable[modid] = $modid";

    if (isset($itemtype) && is_numeric($itemtype)) {
        $sql .= " AND $ctable[itemtype]=$itemtype";
    }

    $result = $dbconn->Execute($sql);
    if (!$result) return;

    // if it's an empty set, return array()
    if ($result->EOF) {
        return array();
    }

    // zip through the list of results and
    // create the return array
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $ret[$row['pageid']]['pageid'] = $row['pageid'];
        $result->MoveNext();
    }
    $result->Close();

    return $ret;

}

?>
