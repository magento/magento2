<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Action;

use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class FullTest extends TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $indexerFactoryMock = $this->createMock(
            StockFactory::class
        );
        $resourceMock = $this->createMock(ResourceConnection::class);
        $productTypeMock = $this->createMock(Type::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $productTypeMock
            ->method('getTypesByPriority')
            ->willReturn([]);

        $exceptionMessage = 'exception message';

        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $resourceMock->expects($this->any())
            ->method('getTableName')
            ->willThrowException(new \Exception($exceptionMessage));

        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Full::class,
            [
                'resource' => $resourceMock,
                'indexerFactory' => $indexerFactoryMock,
                'catalogProductType' => $productTypeMock,
            ]
        );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $model->execute();
    }
}
