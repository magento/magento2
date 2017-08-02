<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

/**
 * Interface for generation of a class of specific type
 * @since 2.2.0
 */
interface GeneratorInterface
{
    /**
     * Generate the requested class if it's supported
     *
     * @param string $className
     * @return mixed
     * @since 2.2.0
     */
    public function generate($className);
}
