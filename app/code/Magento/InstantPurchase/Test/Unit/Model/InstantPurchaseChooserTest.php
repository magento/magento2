<?php

namespace Magento\InstantPurchase\Test\Unit\Model;

use Magento\InstantPurchase\Model\InstantPurchaseChooser;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InstantPurchase\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Model\Customer;
use Magento\InstantPurchase\Model\InstantPurchaseOptionFactory;

class InstantPurchaseChooserTest extends TestCase
{
    /**
     * @var InstantPurchaseChooser
     */
    private $instantPurchaseChooser;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var InstantPurchaseOptionFactory|MockObject
     */
    private $optionFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionFactoryMock = $this->getMockBuilder(InstantPurchaseOptionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->instantPurchaseChooser = $objectManager->getObject(
            InstantPurchaseChooser::class,
            [
                'config' => $this->configMock,
                'instantPurchaseOptionFactory' => $this->optionFactoryMock,
            ]
        );
    }

    public function testGetOptionDisabled()
    {
        $this->configMock->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(false);

        $this->optionFactoryMock->expects($this->once())
            ->method('createDisabledOption');

        $this->instantPurchaseChooser->getOption(
            $this->storeMock,
            $this->customerMock
        );
    }

    public function testGetOption()
    {
        $this->configMock->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $this->optionFactoryMock->expects($this->once())
            ->method('create');

        $this->instantPurchaseChooser->getOption(
            $this->storeMock,
            $this->customerMock
        );
    }
}
