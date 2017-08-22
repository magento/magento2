<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AfterAddressSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    
    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * Module manager
     *
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheConfigMock;

    /**
     * @var \Magento\Weee\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $weeeHelperMock;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \Magento\Weee\Observer\AfterAddressSave
     */
    protected $session;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->weeeHelperMock = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->taxHelperMock = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->objectManager->getObject(
            \Magento\Weee\Observer\AfterAddressSave::class,
            [
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'taxHelper' => $this->taxHelperMock,
            ]
        );
    }

    /**
     * @test
     * @dataProvider getExecuteDataProvider
     *
     * @param $isEnabledPageCache
     * @param $isEnabledConfigCache
     * @param $isEnabledWeee
     * @param $isNeedSetAddress
     */
    public function testExecute(
        $isEnabledPageCache,
        $isEnabledConfigCache,
        $isEnabledWeee,
        $isNeedSetAddress
    ) {
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($isEnabledPageCache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledConfigCache);

        $this->weeeHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledWeee);

        /* @var \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->any())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->taxHelperMock->expects($isNeedSetAddress ? $this->once() : $this->never())
            ->method('setAddressCustomerSessionAddressSave')
            ->with($address);
        
        $this->session->execute($this->observerMock);
    }

    public function getExecuteDataProvider()
    {
        return [
            [false, false, false, false],
            [false, false, true, false],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
        ];
    }
}
