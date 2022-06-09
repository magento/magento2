<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResourceModel;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory as OperationCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testSetIdFieldNameValue(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $classObject = $objectManager->create(
            OperationCollectionFactory::class,
            [
                Bootstrap::getObjectManager()
            ]
        );
        $this->assertNotNull($classObject);
        $collection = $classObject->create();
        $this->assertNotNull($collection);
        $this->assertEquals(OperationResourceModel::TABLE_PRIMARY_KEY, $collection->getIdFieldName());
    }
}
