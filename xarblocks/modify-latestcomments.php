<?php
/**
 * Comments module -  Modify latest comments
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
 * Modify Function to the latestcomments
 * @author jojodee
 * @param $blockinfo array containing title,content
 */
function comments_latestcommentsblock_modify($blockinfo)
{
    // Get current content
    if (!is_array($blockinfo['content'])) {
        $vars = unserialize($blockinfo['content']);
    } else {
        $vars = $blockinfo['content'];
    }
    // get the list of modules+itemtypes that comments is hooked to
    $hookedmodules = xarModAPIFunc('modules', 'admin', 'gethookedmodules',
                                   array('hookModName' => 'comments'));

    $modlist = array();
    $modlist['all'] = xarML('All');
    if (isset($hookedmodules) && is_array($hookedmodules)) {
        foreach ($hookedmodules as $modname => $value) {
            // Get the list of all item types for this module (if any)
            $mytypes = xarModAPIFunc($modname,'user','getitemtypes',
                                     // don't throw an exception if this function doesn't exist
                                     array(), 0);
            // we have hooks for individual item types here
            if (!isset($value[0])) {
                foreach ($value as $itemtype => $val) {
                    if (isset($mytypes[$itemtype])) {
                        $type = $mytypes[$itemtype]['label'];
                    } else {
                        $type = xarML('type #(1)',$itemtype);
                    }
                    $modlist["$modname.$itemtype"] = ucwords($modname) . ' - ' . $type;
                }
            } else {
                $modlist[$modname] = ucwords($modname);
                // allow selecting individual item types here too (if available)
                if (!empty($mytypes) && count($mytypes) > 0) {
                    foreach ($mytypes as $itemtype => $mytype) {
                        if (!isset($mytype['label'])) continue;
                        $modlist["$modname.$itemtype"] = ucwords($modname) . ' - ' . $mytype['label'];
                    }
                }
            }
        }
    }
    $vars['modlist']= $modlist;


    // Return output
    return $vars;
}


/**
 * Updates the Block config from the modify comments block
 * @param $blockinfo array containing title,content
 */
function comments_latestcommentsblock_update($blockinfo)
{
   $vars = array();

    if (!xarVarFetch('howmany', 'int:1:', $vars['howmany'], 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('modid', 'isset', $vars['modid'],  array(), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('pubtypeid', 'isset', $vars['pubtypeid'], 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addauthor', 'isset', $vars['addauthor'], '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addmodule', 'isset', $vars['addmodule'], '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addcomment', 'isset', $vars['addcomment'], '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addobject', 'isset', $vars['addobject'], '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('adddate', 'checkbox', $vars['adddate'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('adddaysep', 'checkbox', $vars['adddaysep'], false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('truncate', 'int:1:', $vars['truncate'], 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addprevious', 'checkbox', $vars['addprevious'], false, XARVAR_NOT_REQUIRED)) return;

    $blockinfo['content'] = $vars;

    return $blockinfo;

}

?>