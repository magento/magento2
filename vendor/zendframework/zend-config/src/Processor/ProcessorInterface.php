<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Processor;

use Zend\Config\Config;

interface ProcessorInterface
{
    /**
     * Process the whole Config structure and recursively parse all its values.
     *
     * @param  Config $value
     * @return Config
     */
    public function process(Config $value);

    /**
     * Process a single value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue($value);
}
