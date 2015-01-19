<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $eavDecimalFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\DecimalFactory',
            ['create'],
            [],
            '',
            false
        );
        $eavSourceFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\SourceFactory',
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

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full(
            $eavDecimalFactory,
            $eavSourceFactory
        );

        $this->setExpectedException('\Magento\Catalog\Exception', $exceptionMessage);

        $model->execute();
    }
}
