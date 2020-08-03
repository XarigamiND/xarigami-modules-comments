<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Comments module
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/projects/xarigami_comments
 */
/**
 * handle static text property
 *
 * @package dynamicdata
 */
sys::import('modules.dynamicdata.class.properties');

class Dynamic_CommentsNumberOf_Property extends Dynamic_Property
{
    public $id         = 104;
    public $name       = 'numcomments';
    public $desc       = 'Number of comments';

    function __construct($args)
    {
        parent::__construct($args);
        $this->tplmodule = 'base';

    }

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
                            'id'         => 104,
                            'name'       => 'numcomments',
                            'label'      => 'Comment number',
                            'format'     => '104',
                            'validation' => 'comments_userapi_get_count',
                            'source'     => 'user function',
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
