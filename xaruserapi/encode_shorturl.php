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
 * return the path for a short URL to xarModURL for this module
 *
 * @author the Comments module development team
 * @param $args the function and arguments passed to xarModURL
 * @returns string
 * @return path to be added to index.php for a short URL, or empty if failed
 */
function comments_userapi_encode_shorturl($args)
{
    // Get arguments from argument array
    extract($args);

    // Check if we have something to work with
    if (!isset($func)) {
        return;
    }

    // Note : make sure you don't pass the following variables as arguments in
    // your module too - adapt here if necessary

    // default path is empty -> no short URL
    $path = '';
    // if we want to add some common arguments as URL parameters below
    $join = '?';
    // we can't rely on xarModGetName() here -> you must specify the modname !
    $module = 'comments';

    // specify some short URLs relevant to your module
    if ($func == 'display') {
        // check for required parameters
        if (!empty($cid) && is_numeric($cid)) {
            $path = '/' . $module . '/' . $cid;
        }
    } else {
        // anything else that you haven't defined a short URL equivalent for
        // -> don't create a path here
    }

    // add some other module arguments as standard URL parameters
    if (!empty($path) && isset($startnum)) {
        $path .= $join . 'startnum=' . $startnum;
    }

    return $path;
}

?>