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
 */
$modversion['name'] = 'comments';
$modversion['id'] = '14';
$modversion['version'] = '1.6.2';
$modversion['displayname']    = 'Comments';
$modversion['description'] = 'Create and post comments on hooked items';
$modversion['credits'] = 'xardocs/credits.txt';
$modversion['help'] = '';
$modversion['changelog'] = 'xardocs/changelog.txt';
$modversion['license'] = '';
$modversion['official'] = 1;
$modversion['author'] = '2skies.com based on original module by Carl P. Corliss (aka Rabbitt)';
$modversion['contact'] = 'http://xarigami.com';
$modversion['homepage'] = 'http://xarigami.com/project/xarigami_comments';
$modversion['admin'] = 1;
$modversion['user'] = 0;
$modversion['class'] = 'Utility';
$modversion['category'] = 'Content';
$modversion['dependencyinfo']   = array(
                                    0 => array(
                                            'name' => 'core',
                                            'version_ge' => '1.4.0'
                                         )
                                );
if (false) { //Load and translate once
    xarML('Comments');
    xarML('Create and post comments on hooked items');
}
?>