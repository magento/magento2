<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Mvc\Console;

/**
 * Extending ZF RouteMatcher for a public getter
 */
class RouteMatcher extends \Zend\Console\RouteMatcher\DefaultRouteMatcher
{
    /**
     * Public getter of parts, used for parameters validation
     *
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }
}
