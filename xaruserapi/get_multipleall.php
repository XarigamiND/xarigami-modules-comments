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
 * Get a list of comments from one or several modules + item types
 *
 * @author Andrea Moro modified from Carl P. Corliss (aka rabbitt) userapi
 * @access public
 * @param array    $modarray   array of module names + itemtypes to look for
 * @param string   $order      sort order (ASC or DESC date)
 * @param integer  $howmany    number of comments to retrieve
 * @param integer  $first      start number
 * @returns array     an array of comments or an empty array if no comments
 *                   found for the particular modules, or raise an
 *                   exception and return false.
 */
function comments_userapi_get_multipleall($args)
{
    extract($args);

    // $modid
    if (!isset($modarray) || empty($modarray) || !is_array($modarray)) {
        $modarray=array('all');
    }
    if (empty($order) || $order != 'ASC') {
        $order = 'DESC';
    } else {
        $order = 'ASC';
    }
    $where = isset($where)?$where :_COM_STATUS_ALL;

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = $xartable['comments_column'];

    switch ((int)$status) {
        case _COM_STATUS_ON:
            $where_status = "$ctable[status] = ". _COM_STATUS_ON;
            break;
        case _COM_STATUS_OFF:
            $where_status = "$ctable[status] = ". _COM_STATUS_OFF;
            break;
        case _COM_STATUS_SPAM :
            $where_status = "$ctable[status] = ". _COM_STATUS_SPAM;
            break;
        case _COM_STATUS_ALL :
        default:
            $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE ." AND $ctable[status] != ". _COM_STATUS_NODE_LOCKED ;
   }

    $commentlist = array();
    $bindvars = array();
    $query = "SELECT  $ctable[title] AS xar_subject,
                      $ctable[comment] AS xar_text,
                      $ctable[cdate] AS xar_datetime,
                      $ctable[author] AS xar_author,
                      $ctable[cid] AS xar_cid,
                      $ctable[status] AS xar_status,
                      $ctable[postanon] AS xar_postanon,
                      $ctable[modid] AS xar_modid,
                      $ctable[itemtype] AS xar_itemtype,
                      $ctable[objectid] AS xar_objectid,
                      $ctable[pid] AS xar_pid
                FROM  $xartable[comments]
                WHERE   $where_status";
    if (count($modarray) > 0 && $modarray[0] != 'all'  ) {
        $where = array();
        foreach ($modarray as $modname) {
            if (strstr($modname,'.')) {
                list($module,$itemtype) = explode('.',$modname);
                $modid = xarModGetIDFromName($module);
                if (empty($itemtype)) {
                    $itemtype = 0;
                }
                $where[] = "($ctable[modid] = $modid AND $ctable[itemtype] = $itemtype)";

            } else {
                $modid = xarModGetIDFromName($modname);
                $where[] = "($ctable[modid] = $modid)";

            }
        }
        if (count($where) > 0) {
            $query .= " AND ( " . join(' OR ', $where) . " ) ";
        }
    }

    $query .= " ORDER BY xar_datetime $order ";

    if (empty($howmany) || !is_numeric($howmany)) {
        $howmany = 5;
    }
    if (empty($first) || !is_numeric($first)) {
        $first = 1;
    }
    if ($howmany >0) {
        $result = $dbconn->SelectLimit($query, $howmany, $first - 1);
    } else {
        $result = $dbconn->SelectLimit($query);
    }
    if (!$result) return;

    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        //jojo - was going to add a security check here but is could be a real performance hit on sites with lots of comments
        //for now let the module handle access to the itemtype and object
        //and put sec checks in comment owned gui functions eg rss.php
         $row['xar_date'] = $row['xar_datetime'];
        //please let's pass back uid  - set it before xarauthor is overwritten
         $row['xar_uid'] = $row['xar_author'];
         if (($row['xar_author'])>1) {
            //jojo - ugly fix but what are we doing to do about this? Should not be here to start with
            $row['xar_author'] = xarUserGetVar('name',$row['xar_author']);
         } else {
            $row['xar_author'] ='';
         }
        $commentlist[] = $row;

        $result->MoveNext();
    }
    $result->Close();

    return $commentlist;
}

?>