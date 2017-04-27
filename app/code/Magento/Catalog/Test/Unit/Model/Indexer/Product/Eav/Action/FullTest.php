<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $eavDecimalFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $eavSourceFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $eavDecimalFactory->expects($this->once())
            ->method('create')
            ->will($this->throwException($exception));

        $metadataMock = $this->getMock(\Magento\Framework\EntityManager\MetadataPool::class, [], [], '', false);
        $batchProviderMock = $this->getMock(\Magento\Framework\Indexer\BatchProviderInterface::class);

        $batchManagementMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator::class,
            [],
            [],
            '',
            false
        );

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full(
            $eavDecimalFactory,
            $eavSourceFactory,
            $metadataMock,
            $batchProviderMock,
            $batchManagementMock,
            []
        );

        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, $exceptionMessage);

        $model->execute();
    }
}
