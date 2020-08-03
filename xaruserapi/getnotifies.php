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
 * Get a notify
 *
 * @param int    $args['user_id'] user_id //xar userid
 * @param id     $args['object_id'] the id of the item
 * @param id     $args['parent_id'] the parent id
 * @param id     $args['itemtype'] the itemtype
 * @param id     $args['module_id'] the module
 * @param int    $args['notifytype'] 1 all //plans for more types 2
 * @return boolean false on failure
 */
function comments_userapi_getnotifies($args)
{
    extract($args);

    // Argument check
    if (empty($object_id) && empty($user_id) && empty($notifytype)) { //1 is an itemtype object subscription, 2 is a thread/parent_id sub
        return;
    }

    if (!isset($sort) || !in_array($sort,$valid)) {
        $sort = 'xar_id';
    }

    if (!isset($parent_id)) {
       $parent_id = 0;
    }
    if (!isset($itemtype)) {
        $itemtype =0;
    }

    if (!isset($notifytype)) {
        $notifytype =NULL;
    }

    if (!isset($startnum) || !is_numeric($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems) || !is_numeric($numitems)) {
        $numitems = -1;
    }
    if (!isset($sortorder) || (isset($sortorder) && !in_array($sortorder, array('DESC','ASC')))) {
        $sortorder = 'ASC';
    }

    $items = array();

    $bindvars = array();

        $wherelist = array();
        // Default issue fields
        $fieldlist = array('user_id','parent_id','module_id','itemtype','object_id','notifytype');
        foreach ($fieldlist as $field) {
            if (!empty($$field)) { //we want empty, not !isset ...
                $wherelist[] = "xar_$field = ?";
                $bindvars[] = (int)$$field;
            }
        }
        if (count($wherelist) > 0) {
            $where = "WHERE " . join(' AND ',$wherelist);
        } else {
            $where = '';
        }
  //  }
    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $notifyTable = $xartable['comments_notify'];

    $query = "SELECT  xar_id,
                      xar_user_id,
                      xar_parent_id,
                      xar_module_id,
                      xar_itemtype,
                      xar_object_id,
                      xar_notifytype,
                      xar_extra ";

    $query .= " FROM $notifyTable
                            $where ";

    $query .= " ORDER BY $sort ";
    $query .= " $sortorder";
    if (isset($numitems) && is_numeric($numitems)) {
        $result = $dbconn->SelectLimit($query, $numitems, $startnum-1,$bindvars);
    } else {
        $result = $dbconn->Execute($query,$bindvars);
    }

    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) return;

    // Put items into result array.
    for (; !$result->EOF; $result->MoveNext()) {

       list($id,$user_id,$parent_id, $module_id, $itemtype, $object_id, $notifytype, $extra)= $result->fields;
             $useremail = xarUserGetVar('email',$user_id);
             $notifies[] = array('id'   => $id,
                                 'user_id'     => $user_id,
                                 'parent_id'   => $parent_id,
                                 'module_id'   => $module_id,
                                 'itemtype'    => $itemtype,
                                 'object_id'   => $object_id,
                                 'notifytype'  => $notifytype,
                                 'extra'       => $extra);
    }
    $result->Close();
    if (isset($notifies) && is_array($notifies)) {

       return $notifies;
    } else {

       return;
    }

}

?>