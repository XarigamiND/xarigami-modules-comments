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
 * Grab the left and right values for each object of a particular module
 *
 * @access   public
 * @param    integer     $modid     id of the module to gather info on
 * @returns  array an array containing the left and right values or an
 *           empty array if the modid specified doesn't exist
 */
function comments_userapi_get_module_lrvalues( $args )
{

    extract ($args);

    if (!isset($modid) || empty($modid)) {
       throw new EmptyParameterException('modid');
    }

    if (empty($itemtype)) {
        $itemtype = 0;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    $sql = "SELECT  $ctable[objectid] AS xar_objectid,
                    MIN($ctable[left]) AS xar_left,
                    MAX($ctable[right]) AS xar_right
              FROM  $xartable[comments]
             WHERE  $ctable[modid]=$modid
               AND  $ctable[itemtype]=$itemtype
          GROUP BY  $ctable[objectid]";

    $result = $dbconn->Execute($sql);

    if(!$result)
        return;

    if (!$result->EOF) {
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $lrvalues[] = $row;
            $result->MoveNext();
        }
    } else {
        $lrvalues = array();
    }

    $result->Close();

    return $lrvalues;
}

?>
