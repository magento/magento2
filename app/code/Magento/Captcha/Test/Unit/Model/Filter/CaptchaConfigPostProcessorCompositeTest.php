<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Model\Filter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;
use Magento\Captcha\Model\Filter\CaptchaConfigPostProcessorComposite;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for Class \Magento\Captcha\Model\Filter\CaptchaConfigPostProcessorComposite
 */
class CaptchaConfigPostProcessorCompositeTest extends TestCase
{
    /**
     * @var CaptchaConfigPostProcessorComposite
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $processorMock1;

    /**
     * @var MockObject
     */
    private $processorMock2;

    /**
     * Initialize Class Dependencies
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->processorMock1 = $this->getMockBuilder(CaptchaConfigPostProcessorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])
            ->getMock();
        $this->processorMock2 = $this->getMockBuilder(CaptchaConfigPostProcessorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])
            ->getMock();

        $processors = [$this->processorMock1, $this->processorMock2];

        $this->model = $this->objectManager->getObject(
            CaptchaConfigPostProcessorComposite::class,
            [
                'processors' => $processors,
            ]
        );
    }

    /**
     * Test for Composite
     *
     * @return void
     */
    public function testProcess(): void
    {
        $config = ['test1','test2', 'test3'];

        $this->processorMock1->expects($this->atLeastOnce())
            ->method('process')
            ->with($config)
            ->willReturn(['test1', 'test2']);
        $this->processorMock2->expects($this->atLeastOnce())
            ->method('process')
            ->with($config)
            ->willReturn(['test3']);

        $this->assertEquals(['test1','test2', 'test3'], $this->model->process($config));
    }
}
