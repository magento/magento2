<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System\Currency\Rate;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    protected function tearDown()
    {
        unset($this->objectManagerHelper);
    }

    public function testPrepareLayout()
    {
        $options = [['value' => 'value', 'label' => 'label']];
        $service = 'service';

        $sourceServiceFactoryMock = $this->getMock(
            'Magento\Directory\Model\Currency\Import\Source\ServiceFactory',
            ['create'],
            [],
            '',
            false
        );
        $sourceServiceMock = $this->getMock(
            'Magento\Directory\Model\Currency\Import\Source\Service',
            [],
            [],
            '',
            false
        );
        $backendSessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['getCurrencyRateService'],
            [],
            '',
            false
        );

        /** @var $layoutMock \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
        $layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            false,
            false,
            true,
            ['createBlock']
        );

        $blockMock = $this->getMock(
            'Magento\Framework\View\Element\Html\Select',
            ['setOptions', 'setId', 'setName', 'setValue', 'setTitle'],
            [],
            '',
            false
        );

        $layoutMock->expects($this->once())->method('createBlock')->willReturn($blockMock);

        $sourceServiceFactoryMock->expects($this->once())->method('create')->willReturn($sourceServiceMock);
        $sourceServiceMock->expects($this->once())->method('toOptionArray')->willReturn($options);
        $backendSessionMock->expects($this->once())->method('getCurrencyRateService')->with(true)->willReturn($service);

        $blockMock->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $blockMock->expects($this->once())->method('setId')->with('rate_services')->willReturnSelf();
        $blockMock->expects($this->once())->method('setName')->with('rate_services')->willReturnSelf();
        $blockMock->expects($this->once())->method('setValue')->with($service)->willReturnSelf();
        $blockMock->expects($this->once())->method('setTitle')->with('Import Service')->willReturnSelf();

        /** @var $block \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services */
        $block = $this->objectManagerHelper->getObject(
            'Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services',
            [
                'srcCurrencyFactory' => $sourceServiceFactoryMock,
                'backendSession' => $backendSessionMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
