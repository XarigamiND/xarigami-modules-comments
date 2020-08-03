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
 * @copyright (C) 2008 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Pass table names back to the framework
 * @return array
 */
function comments_xartables()
{
    // Initialise table array
    $xartable = array();

    // Name for template database entities
    $comments_table     = xarDBGetSiteTablePrefix() . '_comments';
    $comments_notify_table    = xarDBGetSiteTablePrefix() . '_comments_notify';
    // Table name
    $xartable['comments']   = $comments_table;

    $xartable['comments_notify']  = $comments_notify_table;
    
    // Column names
    $xartable['comments_column'] = array('cid'      => $comments_table . '.xar_cid',
                                         'pid'      => $comments_table . '.xar_pid',
                                         'modid'    => $comments_table . '.xar_modid',
                                         'itemtype' => $comments_table . '.xar_itemtype',
                                         'objectid' => $comments_table . '.xar_objectid',
                                         'cdate'    => $comments_table . '.xar_date',
                                         'author'   => $comments_table . '.xar_author',
                                         'title'    => $comments_table . '.xar_title',
                                         'hostname' => $comments_table . '.xar_hostname',
                                         'comment'  => $comments_table . '.xar_text',
                                         'left'     => $comments_table . '.xar_left',
                                         'right'    => $comments_table . '.xar_right',
                                         'status'   => $comments_table . '.xar_status',
                                         'postanon' => $comments_table . '.xar_anonpost'
                                        );



   // Return table information
    return $xartable;
}
?>