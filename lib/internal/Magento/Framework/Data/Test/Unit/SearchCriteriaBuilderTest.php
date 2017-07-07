<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

class SearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $interface = \Magento\Framework\Api\CriteriaInterface::class;

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $factory = $this->getMock(\Magento\Framework\Data\ObjectFactory::class, [], [], '', false);
        $builder = $objectManager->getObject(
            \Magento\Framework\Data\Test\Unit\Stub\SearchCriteriaBuilder::class,
            ['objectFactory' => $factory]
        );
        $factory->expects($this->once())
            ->method('create')
            ->with($interface, ['queryBuilder' => $builder]);

        $builder->make();
    }
}
