<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Comments API
 * @package Xaraya
 * @subpackage Comments_API
 */

include_once('modules/comments/xarincludes/defines.php');

/**
 * Comments Initialization Function
 *
 * @author Carl P. Corliss (aka Rabbitt)
 *
 */
function comments_init()
{

    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    // Create tables
    $ctable = $xartable['comments'];
    $cctable = $xartable['comments_column'];

    $fields = array(
        'xar_cid'       => array('type'=>'integer',  'null'=>FALSE,  'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_pid'       => array('type'=>'integer',  'null'=>FALSE),
        'xar_modid'     => array('type'=>'integer',  'null'=>TRUE),
        'xar_itemtype'  => array('type'=>'integer',  'null'=>false),
        'xar_objectid'  => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
        'xar_date'      => array('type'=>'integer',  'null'=>FALSE),
        'xar_author'    => array('type'=>'integer',  'null'=>FALSE,  'size'=>'medium','default'=>1),
        'xar_title'     => array('type'=>'varchar',  'null'=>FALSE,  'size'=>100),
        'xar_hostname'  => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
        'xar_text'      => array('type'=>'text',     'null'=>TRUE,   'size'=>'medium'),
        'xar_left'      => array('type'=>'integer',  'null'=>FALSE),
        'xar_right'     => array('type'=>'integer',  'null'=>FALSE),
        'xar_status'    => array('type'=>'integer',  'null'=>FALSE,  'size'=>'tiny'),
        'xar_anonpost'  => array('type'=>'integer',  'null'=>TRUE,   'size'=>'tiny', 'default'=>0),
    );

    $query = xarDBCreateTable($xartable['comments'], $fields);

    $result = $dbconn->Execute($query);
    if (!$result)
        return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_left',
                   'fields'    => array('xar_left'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_right',
                   'fields'    => array('xar_right'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_pid',
                   'fields'    => array('xar_pid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_modid',
                   'fields'    => array('xar_modid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_itemtype',
                   'fields'    => array('xar_itemtype'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_objectid',
                   'fields'    => array('xar_objectid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_status',
                   'fields'    => array('xar_status'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_author',
                   'fields'    => array('xar_author'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result = $dbconn->Execute($query);
    if (!$result) return;


    include_once('modules/comments/xarincludes/defines.php');
    xarModSetVar('comments','render',_COM_VIEW_THREADED);
    xarModSetVar('comments','sortby',_COM_SORTBY_THREAD);
    xarModSetVar('comments','order',_COM_SORT_ASC);
    xarModSetVar('comments','depth', _COM_MAX_DEPTH);
    xarModSetVar('comments','AllowPostAsAnon',1);
    xarModSetVar('comments','AuthorizeComments',0); // Not used anywhere useful
    xarModSetVar('comments','AllowCollapsableThreads',1);
    xarModSetVar('comments','CollapsedBranches',serialize(array()));
    xarModSetVar('comments','editstamp',1);
    xarModSetVar('comments','usersetrendering',false);
    // TODO: add delete hook

    // display hook
    if (!xarModRegisterHook('item', 'display', 'GUI','comments', 'user', 'display'))
        return false;

    // usermenu hook
    if (!xarModRegisterHook('item', 'usermenu', 'GUI','comments', 'user', 'usermenu'))
        return false;

    // search hook
    if (!xarModRegisterHook('item', 'search', 'GUI','comments', 'user', 'search'))
        return false;

    // module delete hook
    if (!xarModRegisterHook('module', 'remove', 'API','comments', 'admin', 'remove_module'))
        return false;


    /**
     * Define instances for this module
     * Format is
     * setInstance(Module, Type, ModuleTable, IDField, NameField,
     *             ApplicationVar, LevelTable, ChildIDField, ParentIDField)
     *
     */

    $query1 = "SELECT DISTINCT $xartable[modules].xar_name
                          FROM $ctable
                     LEFT JOIN $xartable[modules]
                            ON $cctable[modid] = $xartable[modules].xar_regid";

    $query2 = "SELECT DISTINCT $cctable[objectid]
                          FROM $ctable";

    $query3 = "SELECT DISTINCT $cctable[cid]
                          FROM $ctable
                         WHERE $cctable[status] != '"._COM_STATUS_ROOT_NODE."'";
    $instances = array(
                        array('header' => 'Module:',
                                'query' => $query1,
                                'limit' => 20
                            ),
                        array('header' => 'Module Page ID:',
                                'query' => $query2,
                                'limit' => 20
                            ),
                        array('header' => 'Comment ID:',
                                'query' => $query3,
                                'limit' => 20
                            )
                    );
    xarDefineInstance('comments','All',$instances);

    /*
     * Register the module components that are privileges objects
     * Format is
     * xarregisterMask(Name,Realm,Module,Component,Instance,Level,Description)
     *
     */

    xarRegisterMask('Comments-Read',     'All','comments', 'All','All:All:All','ACCESS_READ',      'See and Read Comments');
    xarRegisterMask('Comments-Post',     'All','comments', 'All','All:All:All','ACCESS_COMMENT',   'Post a new Comment');
    xarRegisterMask('Comments-Reply',    'All','comments', 'All','All:All:All','ACCESS_COMMENT',   'Reply to a Comment');
    xarRegisterMask('Comments-Edit',     'All','comments', 'All','All:All:All','ACCESS_EDIT',      'Edit Comments');
    xarRegisterMask('Comments-Delete',   'All','comments', 'All','All:All:All','ACCESS_DELETE',    'Delete a Comment or Comments');
    xarRegisterMask('Comments-Moderator','All','comments', 'All','All:All:All','ACCESS_MODERATE',  'Moderate Comments');
    xarRegisterMask('Comments-Admin',    'All','comments', 'All','All:All:All','ACCESS_ADMIN',     'Administrate Comments');


    // Register blocks
    if (!xarModAPIFunc('blocks', 'admin', 'register_block_type',
                       array('modName'  => 'comments',
                             'blockType'=> 'latestcomments'))) return;
    // TODO: define blocks mask & instances here, or re-use some common one ?

 /* This init function brings our module to version 1.3.0, run the upgrades for the rest of the initialisation */
    return comments_upgrade('1.3.0');
}

function comments_delete()
{
    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();

    // Get database information
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    // Delete tables
    $query = xarDBDropTable($xartable['comments']);
    $result = $dbconn->Execute($query);

    if(!$result)
        return;


    // Delete module variables
    xarModDelAllVars('comments');

    if (!xarModUnregisterHook('item', 'display', 'GUI',
                            'comments', 'user', 'display')) {
        return false;
    }
    if (!xarModUnregisterHook('module', 'modifyconfig', 'GUI',
                              'comments', 'admin', 'modifyconfighook')) {
        return false;
    }
    if (!xarModUnregisterHook('module', 'updateconfig', 'API',
                              'comments', 'admin', 'updateconfighook')) {
        return false;
    }

    if (!xarModUnregisterHook('item', 'usermenu', 'GUI','comments', 'user', 'usermenu'))
        return false;

    // search hook
    if (!xarModUnregisterHook('item', 'search', 'GUI','comments', 'user', 'search'))
        return false;

    // module delete hook
    if (!xarModUnregisterHook('module', 'remove', 'API','comments', 'admin', 'remove_module'))
        return false;

    // Remove Masks and Instances
    xarRemoveMasks('comments');
    xarRemoveInstances('comments');

    // UnRegister blocks
    if (!xarModAPIFunc('blocks', 'admin', 'unregister_block_type',
                       array('modName'  => 'comments',
                             'blockType'=> 'latestcomments'))) return;
    return true;
}

/**
* upgrade the comments module from an old version
*/
function comments_upgrade($oldversion)
{
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    xarDBLoadTableMaintenanceAPI();
    $commentstable = $xartable['comments'];
    $moduletable = $xartable['modules'];
    // Upgrade dependent on old version number
    switch($oldversion) {
        case '1.0':
            // Code to upgrade from version 1.0 goes here
            // Register blocks
        if (!xarModAPIFunc('blocks', 'admin', 'block_type_exists',
                               array('modName'  => 'comments',
                                     'blockType'=> 'latestcomments'))) {
                 if (!xarModAPIFunc('blocks', 'admin', 'register_block_type',
                               array('modName'  => 'comments',
                                     'blockType'=> 'latestcomments'))) return;
        }
            // fall through to the next upgrade
        case '1.1':
            // Code to upgrade from version 1.1 goes here
            if (xarModIsAvailable('articles')) {
                // load API for table definition etc.
                if (!xarModAPILoad('articles','user')) return;
            }
            $commentstable = $xartable['comments'];

            // add the xar_itemtype column
            $query = xarDBAlterTable($commentstable,
                                     array('command' => 'add',
                                           'field' => 'xar_itemtype',
                                           'type' => 'integer',
                                           'null' => false,
                                           'default' => '0'));
            $result = $dbconn->Execute($query);
            if (!$result) return;

            // make sure all current records have an itemtype 0 (just in case)
            $query = "UPDATE $commentstable SET xar_itemtype = 0";
            $result = $dbconn->Execute($query);
            if (!$result) return;

            // update the itemtype field for all articles
            if (xarModIsAvailable('articles')) {
                $modid = xarModGetIDFromName('articles');
                $articlestable = $xartable['articles'];

                $query = "SELECT xar_aid, xar_pubtypeid FROM $articlestable";
                $result = $dbconn->Execute($query);
                if (!$result) return;

                while (!$result->EOF) {
                    list($aid,$ptid) = $result->fields;
                    $update = "UPDATE $commentstable SET xar_itemtype = $ptid WHERE xar_objectid = '$aid' AND xar_modid = $modid";
                    $test = $dbconn->Execute($update);
                    if (!$test) return;

                    $result->MoveNext();
                }
                $result->Close();
            }

// TODO: any other modules where we need to insert the right itemtype here ?

            // add an index for the xar_itemtype column
            $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_itemtype',
                           'fields'    => array('xar_itemtype'),
                           'unique'    => FALSE);
            $query = xarDBCreateIndex($commentstable,$index);
            $result = $dbconn->Execute($query);
            if (!$result) return;

            // fall through to the next upgrade
        case '1.2':
        case '1.2.0':

            // Create blacklist tables
            // jojo  - removed all blacklist code - now redundant

        case '1.3.0':
            xarModSetVar('comments','dochildcount',true);
             //upgrade to signify removal of block instances
             //match for core sec upgrades for blocks in xarigami

        case '1.3.1':
            /* subscription types
             * 1 - notify on all comments for this  objectid:itemtype:module_id combo //object/item notify eg aid:ptid:articles
             * 2 - notify on  all comments for this parent_id:objectid:itemtype:module_id //thread notify
             */
            $notifyTable = $xartable['comments_notify'];

            $fields =
              array('xar_id'            => array('type' => 'integer', 'null' => false,  'increment' => true, 'primary_key' => true),
                    'xar_user_id'       => array('type' => 'integer',  'size'=>11, 'null' => false),
                    'xar_parent_id'     => array('type' => 'integer',  'size'=>11, 'null' => false),
                    'xar_module_id'     => array('type' => 'integer',  'size'=>11, 'null' => false),
                    'xar_itemtype'      => array('type' => 'integer',  'size'=>11, 'null' => false),
                    'xar_object_id'      => array('type' => 'integer',  'size'=>11, 'null' => false),
                    'xar_notifytype'    => array('type' => 'integer', 'size'=>11, 'null' => false, 'default'=>'1'),
                    'xar_extra'         => array('type' => 'varchar', 'size'=>255, 'null' => false)
                    );

                    $query = xarDBCreateTable($notifyTable, $fields);
                    if (empty($query)) return; // throw back

                    $result = $dbconn->Execute($query);
                    if (!$result) return;
            $index = array(
                'name'      => 'i_' . xarDBGetSiteTablePrefix() . 'comments_notify_user',
                'fields'    => array('xar_user_id'),
                'unique'    => false
                );
                $query = xarDBCreateIndex($notifyTable,$index);
                if (!$result) return;
              //set some new modvar defaults
              xarModSetVar('comments','usecommentnotify',false);
              xarModSetVar('comments','notifyfromemail',xarModGetVar('mail','adminmail'));
              xarModSetVar('comments','notifyfromname',xarModGetVar('mail','adminname'));

        case '1.3.2':
            //define Thread instances so we can deny by module itemtype and object if necessary
            //leave old ones for backward compatibility

            $query1 = "SELECT DISTINCT xar_modid FROM $commentstable";

            $query2 = "SELECT DISTINCT xar_itemtype FROM $commentstable";

            $query3 = "SELECT DISTINCT xar_objectid FROM $commentstable";

            $instances = array(
                        array('header' => 'Module ID:',
                                'query' => $query1,
                                'limit' => 50
                            ),
                        array('header' => 'Itemtype:',
                                'query' => $query2,
                                'limit' => 40
                            ),
                        array('header' => 'Item ID:',
                                'query' => $query3,
                                'limit' => 20
                            )
                    );
            xarDefineInstance('comments','Thread',$instances);
           //Register new masks
           xarRegisterMask('CommentThreadRead',   'All',  'comments', 'Thread','All:All:All', 'ACCESS_READ',    'See and Read Comments');
           xarRegisterMask('CommentThreadPost',   'All',  'comments', 'Thread','All:All:All', 'ACCESS_COMMENT', 'Post a new Comment');
           xarRegisterMask('CommentThreadEdit',   'All',  'comments', 'Thread','All:All:All', 'ACCESS_EDIT',    'Edit Comments');
           xarRegisterMask('CommentThreadDelete', 'All',  'comments', 'Thread','All:All:All', 'ACCESS_DELETE',  'Delete a Comment or Comments');
           xarRegisterMask('CommentThreadModerator','All','comments', 'Thread','All:All:All', 'ACCESS_MODERATE','Moderate Comments');

       case '1.3.3':
            if (!xarModRegisterHook('module', 'modifyconfig', 'GUI',
                                    'comments', 'admin', 'modifyconfighook')) {
                return false;
            }
            if (!xarModRegisterHook('module', 'updateconfig', 'API',
                                    'comments', 'admin', 'updateconfighook')) {
                return false;
            }
            xarModSetVar('comments', 'allowhookoverride', false);
            xarModSetVar('comments', 'edittimelimit', 0);

       case '1.3.5':
            //put in place additional global configuration settings
            //approval vars
             xarModSetVar('comments','approvalrequired',false); //approval required prior to viewing post
             xarModSetVar('comments','approvalifprevious',false); //approval auto if prior approved post
             xarModSetVar('comments','approvalifinfo',false); //approval for anon if they supply name/email
            //other
            xarModSetVar('comments','useavatars',0); //0-no, 1-if available, 2 - gravitars if available

            //auto moderation vars : GLOBAL
            xarModSetVar('comments','moderatewords',''); //list of words used to tag comments for moderation
            xarModSetVar('comments','moderatelinks',false); //hold post for moderation if it contains links
            xarModSetVar('comments','moderatelinknum',2);  //post must contain 2 or more links for holding
            //blacklist
            xarModSetVar('comments','blacklistwords',''); //list of words used to tag comment as spam
            xarModSetVar('comments','numstats',20); //number of comments per page in Review and Stats

       case '1.3.6':
            //Thread locking hook overrides
            xarModSetVar('comments','manuallock',false); //allow manual thread locking per itemtype item
            xarModSetVar('comments','threadlock',false); //turn auto thread lock on
            xarModSetVar('comments','threadlocktime',0); //auto time in days from original item create time
            xarModSetVar('comments','itemtimefield','pubdate'); //datestamp field for autotimelock calculation
       case '1.4.0':
            //support for anonymous posting with name and email
            //change postanon field from tinyint to varchar254  - to do so safely on all db types create new field, copy over, drop old
            //create temp table
            $query = xarDBAlterTable($commentstable,
                              array('command' => 'add',
                                    'field'   => 'xar_tanonpost',
                                    'type'    => 'varchar',
                                    'size'    => '255',
                                    'null'    => false,
                                    'default' => ''));
            // Pass to ADODB, and send exception if the result isn't valid.
            $result = $dbconn->Execute($query);
            if (!$result) return;
            //copy in all xaranonpost
            $allcomments = "SELECT xar_cid, xar_anonpost,xar_tanonpost
                     FROM $commentstable";
            $result = $dbconn->Execute($allcomments);
            if (!$result) return;

           for (; !$result->EOF; $result->MoveNext()) {
              list($cid, $anonpost, $tanonpost) = $result->fields;

              if (!isset($anonpost) || empty($anonpost)) {
                  $newanonpost='0';
              } else {
                  $newanonpost = $anonpost;
              }
              //Copy to temp fields
              $docopy = "UPDATE $commentstable
                          SET xar_tanonpost= $newanonpost
                         WHERE xar_cid   = $cid";
               $doupdate = $dbconn->Execute($docopy);
               if(!$doupdate) return;
           }
           //Drop both the fieldsnow we have them copied, and recreate it
            $query="ALTER TABLE $commentstable DROP xar_anonpost";
            // Pass to ADODB, and send exception if the result isn't valid.
            $result = $dbconn->Execute($query);
            if (!$result) return;
           //make it new
            $query = xarDBAlterTable($commentstable,
                              array('command' => 'add',
                                    'field'   => 'xar_anonpost',
                                    'type'    => 'varchar',
                                    'size'    => '255',
                                    'null'    => false,
                                    'default' => ''));
            // Pass to ADODB, and send exception if the result isn't valid.
            $result = $dbconn->Execute($query);
            if (!$result) return;
            //copy it back ....
            $allcomments = "SELECT xar_cid, xar_anonpost,xar_tanonpost
                     FROM $commentstable";
            $result = $dbconn->Execute($allcomments);
            if (!$result) return;

           for (; !$result->EOF; $result->MoveNext()) {
              list($cid, $anonpost, $tanonpost) = $result->fields;

              if (!isset($tanonpost) || empty($tanonpost)) {
                  $newanonpost='0';
              } else {
                  $newanonpost = $tanonpost;
              }
              //Copy to temp fields
              $docopy = "UPDATE $commentstable
                          SET xar_anonpost= $newanonpost
                         WHERE xar_cid   = $cid";
               $doupdate = $dbconn->Execute($docopy);
               if(!$doupdate) return;
           }

           //Drop the temp field now we have them copied
            $query="ALTER TABLE $commentstable DROP xar_tanonpost";
            // Pass to ADODB, and send exception if the result isn't valid.
            $result = $dbconn->Execute($query);
            if (!$result) return;

       case '1.5.0':
            //only UI changes - up the version to reflect changes
       case '1.5.1':
            //up the version to signify exception changes
       case '1.5.2':
       case '1.6.0':
        //no db change - add back the comment number property
       case '1.6.1': 
            xarModSetvar('comments','holdallanon', TRUE); //hold all anonymous (non-logged in) posts for moderation
       case '1.6.2': //current
            break;
    }
    return true;
}

//
?>
