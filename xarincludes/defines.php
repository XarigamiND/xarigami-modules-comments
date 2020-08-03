<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation 
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2008-2012 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
if (defined('_COM_SORT_ASC')) return;

// the following two defines specify the sorting direction which
// can be either ascending or descending
define('_COM_SORT_ASC', 1);
define('_COM_SORT_DESC', 2);

// the following four defines specify the sort order which can be any of
// the following: author, date, topic, lineage
// TODO: Add Rank sorting
define ('_COM_SORTBY_AUTHOR', 1);
define ('_COM_SORTBY_DATE', 2);
define ('_COM_SORTBY_THREAD', 3);
define ('_COM_SORTBY_TOPIC', 4);

// the following define is for $cid when
// you want to retrieve all comments as opposed
// to entering in a real comment id and getting
// just that specific comment
define('_COM_RETRIEVE_ALL', 1);
define('_COM_VIEW_FLAT', 'flat');
define('_COM_VIEW_NESTED', 'nested');
define('_COM_VIEW_THREADED', 'threaded');

// the following defines are for the $depth variable
// the -1 (FULL_TREE) tells it to get the full
// tree/branch and the the 0 (TREE_LEAF) tells the function
// to acquire just that specific leaf on the tree.
//
define('_COM_FULL_TREE', ((int) '-1'));
define('_COM_TREE_LEAF', 1);

// Maximum allowable branch depth
define('_COM_MAX_DEPTH', 15);

// Status of comment nodes
// 
define('_COM_STATUS_ALL', 0); //all 
define('_COM_STATUS_OFF', 1); //unapproved
define('_COM_STATUS_ON',  2);  //approved
define('_COM_STATUS_ROOT_NODE', 3); //active
define('_COM_STATUS_SPAM', 4); //marked as spam
define('_COM_STATUS_HAM', 5); //marked as ham (temporary status that is moved to 1 or 2 depending on config settings)
define('_COM_STATUS_NODE_LOCKED', 32); //locked

//Notification types
define('_COM_NOTIFY_THREAD',1); //Notification when a post is made in a thread
define('_COM_NOTIFY_REPLY',2); //Notification when a reply is made on a post

?>