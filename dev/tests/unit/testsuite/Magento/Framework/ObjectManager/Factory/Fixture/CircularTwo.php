<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Factory\Fixture;

/**
 * Part of the chain for circular dependency test
 */
class CircularTwo
{
    /**
     * @param CircularThree $three
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(CircularThree $three)
    {
    }
}
