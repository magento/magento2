<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $indexerFactoryMock = $this->getMock(
            'Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory',
            [],
            [],
            '',
            false
        );
        $resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $productTypeMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');

        $exceptionMessage = 'exception message';

        $connectionMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception($exceptionMessage)));

        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\CatalogInventory\Model\Indexer\Stock\Action\Full',
            [
               'resource' => $resourceMock,
               'indexerFactory' => $indexerFactoryMock,
               'catalogProductType' => $productTypeMock,
            ]
        );

        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException', $exceptionMessage);

        $model->execute();
    }
}
