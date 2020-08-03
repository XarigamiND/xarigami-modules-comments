<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
sys::import('modules.comments.xarincludes.defines');
/**
 * Count comments by modid/objectid/all and active/inactive/all
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access  private
 * @param   string  type     What to gather for: ALL, MODULE, or OBJECT (object == modid/objectid pair)
 * @param   string  status   What status' to count: ALL (minus root nodes), ACTIVE, INACTIVE, SPAM
 * @param   integer modid    Module to gather info on (only used with type == module|object)
 * @param   integer itemtype Item type in that module to gather info on (only used with type == module|object)
 * @param   integer objectid ObjectId to gather info on (only used with type == object)
 * @returns integer total comments
 */
function comments_adminapi_count_comments( $args )
{
    extract($args);
    $dbconn         = xarDBGetConn();
    $xartable       = xarDBGetTables();
    $ctable         = $xartable['comments_column'];
    $total          = 0;
    $status         = strtolower($status);
    $type           = strtolower($type);
    $where_type     = '';
    $where_status   = '';

    if (empty($type) || !preg_match('/^(all|module|object)$/i',$type)) {
        $msg = xarML('Invalid Parameter \'type\' to function count_comments(). \'type\' must be: all, module, or object.');
        throw new BadParameterException(null,$msg);
    } else {

        switch ($type) {
            case 'object':
                if (empty($objectid)) {
                    $msg = xarML('Missing or Invalid Parameter \'objectid\'');
                    throw new EmptyParameterException(null,$msg);
                }

                $where_type = "$ctable[objectid] = '$objectid' AND ";

                // Allow the switch to fall through if type == object because
                // we need modid for object in addition to objectid
                // hence, no break statement here :-)

            case 'module':
                if (empty($modid)) {
                    $msg = xarML('Missing or Invalid Parameter \'modid\'');
                    throw new EmptyParameterException(null,$msg);
                }

                $where_type .= "$ctable[modid] = $modid";

                if (isset($itemtype) && is_numeric($itemtype)) {
                    $where_type .= " AND $ctable[itemtype] = $itemtype";
                }
                break;

            default:
            case 'all':
                $where_type = "1";
        }
    }
    if (empty($status) || !preg_match('/^(all|inactive|active)$/i',$status)) {
        $msg = xarML('Invalid Parameter \'status\' to function count_module_comments(). \'status\' must be: all, active, or inactive.');
        throw new BadParameterException(null,$msg);
    } else {
        switch ($status) {
            case 'active':
                $where_status = "$ctable[status] = ". _COM_STATUS_ON;
                break;
            case 'inactive':
                $where_status = "$ctable[status] = ". _COM_STATUS_OFF;
                break;
            case 'spam':
                $where_status = "$ctable[status] = ". _COM_STATUS_SPAM;
                break;
            default:
            case 'active':
                $where_status = "$ctable[status] != ". _COM_STATUS_ROOT_NODE;
        }
    }
    $query = "SELECT COUNT($ctable[cid])
                FROM $xartable[comments]
               WHERE $where_type
                 AND $where_status";
    $result = $dbconn->Execute($query);
    if (!$result)
        return;

    if ($result->EOF) {
        return 0;
    }
    list($numitems) = $result->fields;
    $result->Close();
    return $numitems;
}
?>