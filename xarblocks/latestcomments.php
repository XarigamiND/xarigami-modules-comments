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
/**
 * Original Author of file: Andrea Moro
 * Updated for latest api: Jo dalle Nogare
 * Purpose of file: Show latest posted comments
 * initialise block
 */
function comments_latestcommentsblock_init()
{
    // Initial values when the block is created.
    return array(
                 'howmany' => 5,
                 'modid' => array('all'),
                 'addauthor' => 1,
                 'addmodule' => 0,
                 'addcomment' => 20,
                 'addobject' => 1,
                 'adddate' => 'on',
                 'adddaysep' => 'on',
                 'truncate' => 18,
                 'addprevious' => 'on',
                 'nocache' => 1, // don't cache by default
                 'pageshared' => 1, // but if you do, share across pages
                 'usershared' => 1, // and for group members
                 'cacheexpire' => null
                );
}

/**
 * get information on block
 */
function comments_latestcommentsblock_info()
{
    // Values
    return array('text_type' => 'latestcomments',
                 'module' => 'comments',
                 'text_type_long' => 'Show Latest Comments',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true
                 );
}

/**
 * display block
 */
function comments_latestcommentsblock_display($blockinfo)
{
    if (empty($blockinfo['content'])) {
        return '';
    }

    // Get variables from content block
    if (!is_array($blockinfo['content'])) {
        $blockinfo['content'] = unserialize($blockinfo['content']);
    }
    //reference for easy var collection
    $vars = $blockinfo['content'];

    $vars['block_is_calling']=1;
    $vars['first']=1;
    $vars['order']='DESC';
    //TODO: jojo - we should have status configurable now
    //Tcould be used to show latest submitted comments
    $vars['status']= 2; //approved comments only

     $items = xarModAPIFunc('comments', 'user', 'displayall', $vars) ;

    foreach ($items['commentlist'] as $day=>$itemlist) {
        foreach ($itemlist as $key=>$item) {

            if (xarSecurityCheck('CommentThreadRead',0,'Thread',$item['xar_modid'].":".$item['xar_itemtype'].":".$item['xar_objectid'])) {
               //
            } else {
              unset($items['commentlist'][$day][$key]);
            }
        }

    }
    $vars['anonid'] = xarConfigGetVar('Site.User.AnonymousUID');
    $blockinfo['content'] = $items ;

    return $blockinfo;
}
?>