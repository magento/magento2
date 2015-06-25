<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

class MassStatusTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\View\Result\Redirect */
    protected $resultRedirect;

    protected function setUp()
    {
        $this->priceProcessor = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Price\Processor')
            ->disableOriginalConstructor()->getMock();

        $productBuilder = $this->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Builder')->setMethods([
                'build',
            ])->disableOriginalConstructor()->getMock();

        $product = $this->getMockBuilder('\Magento\Catalog\Model\Product')->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep', '__wakeup'])->getMock();
        $product->expects($this->any())->method('getTypeId')->will($this->returnValue('simple'));
        $product->expects($this->any())->method('getStoreId')->will($this->returnValue('1'));
        $productBuilder->expects($this->any())->method('build')->will($this->returnValue($product));

        $this->resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $additionalParams = ['resultFactory' => $resultFactory];
        $this->action = new \Magento\Catalog\Controller\Adminhtml\Product\MassStatus(
            $this->initContext($additionalParams),
            $productBuilder,
            $this->priceProcessor
        );
    }

    public function testMassStatusAction()
    {
        $this->priceProcessor->expects($this->once())->method('reindexList');
        $this->action->execute();
    }
}
