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

        $this->assertInternalType('array', $map);
        $this->assertArrayHasKey('fields', $map);
        $this->assertInternalType('array', $map['fields']);
        $this->assertCount(count($tableDescription), $map['fields']);

        foreach ($map['fields'] as $mappedName) {
            $this->assertContains('main_table.', $mappedName);
        }
    }
}
