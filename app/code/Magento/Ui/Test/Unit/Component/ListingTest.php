<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Listing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListingTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName(): void
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            Listing::class,
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertSame(Listing::NAME, $listing->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare(): void
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $buttons = [
            'button1' => 'button1',
            'button2' => 'button2'
        ];
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            Listing::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends',
                        'testData' => 'testValue'
                    ],
                    'buttons' => $buttons
                ]
            ]
        );

        $this->contextMock
            ->method('getNamespace')
            ->willReturn(Listing::NAME);
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($listing->getComponentName(), ['extends' => 'test_config_extends', 'testData' => 'testValue']);
        $this->contextMock->expects($this->once())
            ->method('addButtons')
            ->with($buttons, $listing);

        $listing->prepare();
    }
}
