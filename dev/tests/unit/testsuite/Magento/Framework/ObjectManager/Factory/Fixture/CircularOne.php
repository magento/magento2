<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory\Fixture;

/**
 * Part of the chain for circular dependency test
 */
class CircularOne
{
    /**
     * @param CircularTwo $two
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CircularTwo $two)
    {
    }
}
