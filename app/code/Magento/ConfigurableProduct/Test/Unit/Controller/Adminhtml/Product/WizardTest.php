<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Wizard;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WizardTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $resultFactory;

    /**
     * @var MockObject
     */
    private $productBuilder;

    /**
     * @var MockObject
     */
    private $request;

    /**
     * @var Wizard
     */
    private $model;

    protected function setUp(): void
    {
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->productBuilder = $this->createPartialMock(Builder::class, ['build']);
        $this->request = $this->createMock(RequestInterface::class);
        $context = $this->createMock(Context::class);

        $context->method('getResultFactory')->willReturn($this->resultFactory);
        $context->method('getRequest')->willReturn($this->request);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Wizard::class,
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
