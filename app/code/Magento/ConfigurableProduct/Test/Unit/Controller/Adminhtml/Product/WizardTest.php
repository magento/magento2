<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class WizardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $productBuilder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Wizard
     */
    private $model;

    protected function setUp(): void
    {
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productBuilder = $this->getMockBuilder(\Magento\Catalog\Controller\Adminhtml\Product\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactory);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Wizard::class,
            [
                'context' => $context,
                'productBuilder' => $this->productBuilder
            ]
        );
    }

    public function testExecute()
    {
        $this->productBuilder->expects($this->once())->method('build')->with($this->request);
        $this->resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_LAYOUT);

        $this->model->execute();
    }
}
