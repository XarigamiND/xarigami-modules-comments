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
 * Delete a comment or a group of comments
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 * @returns mixed description of return
 */
function comments_user_delete()
{

    if (!xarSecurityCheck('Comments-Delete'))
        return;

    $header = xarRequestGetVar('header');
    $receipt = xarRequestGetVar('receipt');

    // Make sure some action was submitted
    if (!array_key_exists('action', $receipt))
        $receipt['action'] = 'confirm-delete';

    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    switch(strtolower($receipt['action'])) {
        default:
        case 'confirm-delete':

            $comments = xarModAPIFunc('comments','user','get_one',
                                       array('cid' => $header['cid']));

            $header['modid'] = $comments[0]['xar_modid'];
            $header['itemtype'] = $comments[0]['xar_itemtype'];
            $header['objectid'] = $comments[0]['xar_objectid'];

            // get the title and link of the original object
            $modinfo = xarModGetInfo($header['modid']);
            $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                                       array('itemtype' => $header['itemtype'],
                                             'itemids' => array($header['objectid'])),
                                       // don't throw an exception if this function doesn't exist
                                       0);
            if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
                $url = $itemlinks[$header['objectid']]['url'];
                $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
                $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
            } else {
                $url = xarModURL($modinfo['name'],'user','main');
            }
             if (!empty($receipt['returnurl']['review'])) {
             $receipt['returnurl'] = array('encoded' => rawurlencode($receipt['returnurl']['review']),
                                              'decoded' => $receipt['returnurl']['review']);
            }  elseif  (empty($receipt['returnurl'])) {
                $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                              'decoded' => $url);
            }

            break;
        case 'reparent':
            xarModAPIFunc('comments','admin','delete_node',
                          array('node' => $header['cid'],
                                'pid'  => $header['pid']));
            $msg = xarML('Comments successfully deleted.');
            xarTplSetMessage($msg,'status');
            xarResponseRedirect($receipt['returnurl']['decoded']);
            return true;
        case 'thread':
            xarModAPIFunc('comments','admin','delete_branch',
                          array('node' => $header['cid']));
            $msg = xarML('Comment successfully deleted.');
            xarTplSetMessage($msg,'status');
            xarResponseRedirect($receipt['returnurl']['decoded']);
            return true;
    }

    $output['header'] = $header;
    $output['receipt'] = $receipt;
    $output['package']['delete_url'] = xarModURL('comments','user','delete');

    return $output;
}

?>