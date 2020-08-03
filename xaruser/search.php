<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_comments
 */
/**
 * Searches all -active- comments based on a set criteria
 * @access private
 * @returns mixed description of return
 */
function comments_user_search( $args )
{
    if(!xarVarFetch('startnum', 'isset', $startnum,  NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('header',   'isset', $header,    NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('q',        'isset', $q,         NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('bool',     'isset', $bool,      NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('sort',     'isset', $sort,      NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('author',   'isset', $author,    NULL, XARVAR_DONT_SET)) {return;}

    $postinfo   = array('q' => $q, 'author' => $author);
    $data       = array();
    $search     = array();


    if (!isset($q) || strlen(trim($q)) <= 0) {
        if (isset($author) && strlen(trim($author)) > 0) {
            $q = $author;
        } else {
            $data['header']['text']     = 1;
            $data['header']['title']    = 1;
            $data['header']['author']   = 1;
            return $data;
        }
    }
    $q= str_replace('%','\%',$q);
    $q = str_replace('_','\_',$q);
    $q = "%$q%";

    // Default parameters
    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = 20;
    }

    if (isset($header['title'])) {
        $search['title'] = $q;
        $postinfo['header[title]'] = 1;
        $header['title'] = 1;
    } else {
        $header['title'] = 0;
        $postinfo['header[title]'] = 0;
    }

    if (isset($header['text'])) {
        $search['text'] = $q;
        $postinfo['header[text]'] = 1;
        $header['text'] = 1;
    } else {
        $header['text'] = 0;
        $postinfo['header[text]'] = 0;
    }

    if (isset($header['author'])) {
        $postinfo['header[author]'] = 1;
        $header['author'] = 1;
        // Search the uid with the display name
        $user = xarFindRole($author);

        if (!empty($user)) {
            $search['uid'] = $user->getID();
            $search['author'] = $author;
        }
    } else {
        $postinfo['header[author]'] = 0;
        $header['author'] = 0;
    }

    $package['comments'] = xarModAPIFunc('comments', 'user', 'search', $search);

    if (!empty($package['comments'])) {

        foreach ($package['comments'] as $key => $comment) {
            if ($header['text']) {
                // say which pieces of text (array keys) you want to be transformed
                $comment['transform'] = array('xar_text');
                // call the item transform hooks
                // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
                $comment = xarModCallHooks('item', 'transform', $comment['xar_cid'], $comment, 'comments');
                // Index appears to be empty on the transform.  Is this line needed?
                //$package['comments'][$key]['xar_text'] = xarVarPrepHTMLDisplay($comment['xar_text']);
            }
            if ($header['title']) {
                $package['comments'][$key]['xar_title'] = xarVarPrepForDisplay($comment['xar_title']);
            }
        }

        $header['modid'] = $package['comments'][0]['xar_modid'];
        $header['itemtype'] = $package['comments'][0]['xar_itemtype'];
        $header['objectid'] = $package['comments'][0]['xar_objectid'];
        $receipt['returnurl']['decoded'] = xarModURL('comments','user','display', $postinfo);
        $receipt['returnurl']['encoded'] = rawurlencode($receipt['returnurl']['decoded']);

        $receipt['directurl'] = true;

        if (!xarModLoad('comments','renderer')) {
            throw new FunctionNotFoundException('comments_renderer');
        }
    $package['settings'] = xarModAPIFunc('comments','user','getoptions',$header);

        $package['comments'] = comments_renderer_array_prune_excessdepth(
                                  array('array_list' => $package['comments'],
                                        'cutoff'     => $package['settings']['depth'],
                                        'modid'      => $header['modid'],
                                        'itemtype'   => $header['itemtype'],
                                        'objectid'   => $header['objectid'],
                                  )
                               );

        $data['package'] = $package;
        $data['receipt'] = $receipt;


    }

    if (!isset($data['package'])){
        $data['receipt']['status'] = xarML('No Comments Found Matching Search');
    }

    $data['header'] = $header;
    return $data;
}

?>
