<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Ui\Component\Listing;
use Magento\Ui\Component\Listing\Columns;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ListingTest
 */
class ListingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing',
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertTrue($listing->getComponentName() === Listing::NAME);
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        $buttons = [
            'button1' => 'button1',
            'button2' => 'button2'
        ];
        /** @var Listing $listing */
        $listing = $this->objectManager->getObject(
            'Magento\Ui\Component\Listing',
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends',
                        'testData' => 'testValue',
                    ],
                    'buttons' => $buttons
                ]
            ]
        );

        $this->contextMock->expects($this->at(0))
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
