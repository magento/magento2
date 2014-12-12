<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
