<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
