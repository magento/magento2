<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System\Currency\Rate;

use Magento\Backend\Model\Session;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services;
use Magento\Directory\Model\Currency\Import\Source\Service;
use Magento\Directory\Model\Currency\Import\Source\ServiceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase
{
    /**
     * Object manager helper
     *
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
    }

    protected function tearDown(): void
    {
        unset($this->objectManagerHelper);
    }

    public function testPrepareLayout()
    {
        $options = [['value' => 'value', 'label' => 'label']];
        $service = 'service';

        $sourceServiceFactoryMock = $this->createPartialMock(
            ServiceFactory::class,
            ['create']
        );
        $sourceServiceMock = $this->createMock(Service::class);
        $backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getCurrencyRateService'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['createBlock']
        );

        $blockMock = $this->getMockBuilder(Select::class)
            ->addMethods(['setName', 'setValue'])
            ->onlyMethods(['setOptions', 'setId', 'setTitle'])
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())->method('createBlock')->willReturn($blockMock);

        $sourceServiceFactoryMock->expects($this->once())->method('create')->willReturn($sourceServiceMock);
        $sourceServiceMock->expects($this->once())->method('toOptionArray')->willReturn($options);
        $backendSessionMock->expects($this->once())->method('getCurrencyRateService')->with(true)->willReturn($service);

        $blockMock->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $blockMock->expects($this->once())->method('setId')->with('rate_services')->willReturnSelf();
        $blockMock->expects($this->once())->method('setName')->with('rate_services')->willReturnSelf();
        $blockMock->expects($this->once())->method('setValue')->with($service)->willReturnSelf();
        $blockMock->expects($this->once())->method('setTitle')->with('Import Service')->willReturnSelf();

        /** @var Services $block */
        $block = $this->objectManagerHelper->getObject(
            Services::class,
            [
                'srcCurrencyFactory' => $sourceServiceFactoryMock,
                'backendSession' => $backendSessionMock
            ]
        );
        $block->setLayout($layoutMock);
    }
}
