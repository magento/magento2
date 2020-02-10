<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Grid;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests collection properties.
     *
     * @throws \ReflectionException
     * @return void
     */
    public function testCollectionCreate(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var Collection $gridCollection */
        $gridCollection = $objectManager->get(Collection::class);
        $tableDescription = $gridCollection->getConnection()
            ->describeTable($gridCollection->getMainTable());

        $mapper = new \ReflectionMethod(
            Collection::class,
            '_getMapper'
        );
        $mapper->setAccessible(true);
        $map = $mapper->invoke($gridCollection);

        self::assertInternalType('array', $map);
        self::assertArrayHasKey('fields', $map);
        self::assertInternalType('array', $map['fields']);
        self::assertCount(count($tableDescription), $map['fields']);

        foreach ($map['fields'] as $mappedName) {
            self::assertContains('main_table.', $mappedName);
        }
    }
}
