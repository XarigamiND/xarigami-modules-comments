<?php
/**
 * Comments - get notify
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * count notifies
 *
 * @param int    $args['user_id'] user_id //xar userid
 * @param id     $args['object_id'] the id of the item
 * @param id     $args['parent_id'] the parent id
 * @param id     $args['itemtype'] the itemtype
 * @param id     $args['module_id'] the module
 * @param int    $args['notifytype'] 1 all //plans for more types 2
 * @param int    $args['countoff'] true return all items
 * @return boolean false on failure
 */
function comments_userapi_countnotifies($args)
{
    if (!xarSecurityCheck('Comments-Admin')) return;

    extract($args);

    if (!isset($notifytype)) {
        $notifytype = NULL;
    }
    if (!isset($countoff)) {
        $countoff = FALSE;
    }
    $items = array();

    $bindvars = array();

    $wherelist = array();

    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $notifyTable = $xartable['comments_notify'];
    $commentTable = $xartable['comments'];

    // Default issue fields
    $fieldlist = array('user_id','parent_id','module_id','itemtype','object_id','notifytype','status');
    foreach ($fieldlist as $field) {
        if (!empty($$field)) { //we want empty, not !isset ...
            $wherelist[] = "xar_$field = ?";
            $bindvars[] = (int)$$field;
        }
    }
    //handle date
    if (isset($date) && isset($comparison)) {
        $wherelist[] = 'xar_date '.$comparison.' ?';
        $bindvars[] = $date;
    }

   //handle the "join"
    if ((isset($status) && $status = _COM_NOTIFY_THREAD) || isset($date)) {
        $wherelist[] = "$notifyTable.xar_parent_id = $commentTable.xar_pid
                       AND $notifyTable.xar_module_id = $commentTable.xar_modid
                       AND $notifyTable.xar_itemtype = $commentTable.xar_itemtype
                       AND $notifyTable.xar_object_id = $commentTable.xar_objectid" ;
    } elseif  ((isset($status) && $status = _COM_NOTIFY_REPLY) || isset($date)) {
       $wherelist[] = "$notifyTable.xar_module_id = $commentTable.xar_modid
                       AND $notifyTable.xar_itemtype = $commentTable.xar_itemtype
                       AND $notifyTable.xar_object_id = $commentTable.xar_objectid" ;
    }

    if (count($wherelist) > 0) {
        $where = "WHERE " . join(' AND ',$wherelist);
    } else {
        $where = '';
    }

    if (isset($status) || isset($date)) {
        if ($countoff == TRUE) {
            $query  =  "SELECT xar_id";
        } else {
            $query  =  "SELECT COUNT(*) ";
        }
        $query .= " FROM $notifyTable, $commentTable $where";

    } else { //do a straight count
        if ($countoff == TRUE) {
            $query  =  "SELECT xar_id";
        } else {
            $query  =  "SELECT COUNT(*) ";
        }
        $query .= " FROM $notifyTable
                                $where ";
    }

    $result = $dbconn->Execute($query,$bindvars);
    if (!$result) return;
    if ($countoff == TRUE) {
        $numitems = array();
         while (!$result->EOF) {
            list($notifyid) = $result->fields;
            $numitems[] = $notifyid;
            $result->MoveNext();
        }
    } else {
       // Obtain the number of items
        list($numitems) = $result->fields;

    }

    $result->Close();
    // Return the number of items
    return $numitems;
}

?>