<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\ObjectFactory;
use Magento\Framework\Data\Test\Unit\Stub\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SearchCriteriaBuilderTest extends TestCase
{
    public function testMake()
    {
        $interface = CriteriaInterface::class;

        $objectManager = new ObjectManager($this);
        $factory = $this->createMock(ObjectFactory::class);
        $builder = $objectManager->getObject(
            SearchCriteriaBuilder::class,
            ['objectFactory' => $factory]
        );
        $factory->expects($this->once())
            ->method('create')
            ->with($interface, ['queryBuilder' => $builder]);

        $builder->make();
    }
}
