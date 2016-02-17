<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

/**
 * Helper for setting and retrieving script elements for inclusion in HTML body
 * section
 */
class InlineScript extends HeadScript
{
    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = 'Zend_View_Helper_InlineScript';

    /**
     * Return InlineScript object
     *
     * Returns InlineScript helper object; optionally, allows specifying a
     * script or script file to include.
     *
     * @param  string $mode      Script or file
     * @param  string $spec      Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array  $attrs     Array of script attributes
     * @param  string $type      Script type and/or array of script attributes
     * @return InlineScript
     */
    public function __invoke(
        $mode = self::FILE,
        $spec = null,
        $placement = 'APPEND',
        array $attrs = array(),
        $type = 'text/javascript'
    ) {
        return parent::__invoke($mode, $spec, $placement, $attrs, $type);
    }
}
