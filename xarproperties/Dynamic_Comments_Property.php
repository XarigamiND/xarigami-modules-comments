<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments module
 * @copyright (C) 2007-2010 2skies.com
 * @link http://xarigami.com/projects/xarigami_comments
 */
/**
 * handle static text property
 *
 * @package dynamicdata
 *
 */
sys::import('modules.dynamicdata.class.properties');
class Dynamic_Comments_Property extends Dynamic_Property
{
    /**
     * Get the base information for this property.
     *
     * @returns array
     * @return base information for this property
     **/
     function getBasePropertyInfo()
     {
         $args = array();
         $baseInfo = array(
                            'id'         => 103,
                            'name'       => 'comments',
                            'label'      => 'Comments',
                            'format'     => '103',
                            'validation' => '',
                            'source'     => 'hook module',
                            'dependancies' => '',
                            'requiresmodule' => 'comments',
                            'filepath'    => 'modules/comments/xarproperties',
                            'aliases' => '',
                              'args'           => serialize($args),
                            // ...
                           );
        return $baseInfo;
     }

}

?>
