<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

/**
 * Interface for generation of a class of specific type
 *
 * @api
 */
interface GeneratorInterface
{
    /**
     * Generate the requested class if it's supported
     *
     * @param string $className
     * @return mixed
     */
    public function generate($className);
}
