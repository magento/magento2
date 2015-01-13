<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $indexerFactoryMock = $this->getMock(
            'Magento\CatalogInventory\Model\Resource\Indexer\StockFactory',
            [],
            [],
            '',
            false
        );
        $resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $productTypeMock = $this->getMock('Magento\Catalog\Model\Product\Type', [], [], '', false);
        $adapterMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');

        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $adapterMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException($exception));

        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));

        $model = new \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full(
            $resourceMock,
            $indexerFactoryMock,
            $productTypeMock
        );

        $this->setExpectedException('\Magento\CatalogInventory\Exception', $exceptionMessage);

        $model->execute();
    }
}
