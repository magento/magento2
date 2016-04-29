<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Definition;

use \Magento\Framework\ObjectManager\Definition\Compiled;

/**
 * Stub class for abstract Magento\Framework\ObjectManager\DefinitionInterface
 */
class CompiledStub extends Compiled
{
    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return unserialize($signature);
    }
}
