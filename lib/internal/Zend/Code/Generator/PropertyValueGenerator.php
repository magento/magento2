<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class PropertyValueGenerator extends ValueGenerator
{

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return parent::generate() . ';';
    }

}
