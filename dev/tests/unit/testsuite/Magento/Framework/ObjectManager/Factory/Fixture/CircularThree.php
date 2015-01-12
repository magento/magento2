<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory\Fixture;

/**
 * Part of the chain for circular dependency test
 */
class CircularThree
{
    /**
     * @param CircularOne $one
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CircularOne $one)
    {
    }
}
