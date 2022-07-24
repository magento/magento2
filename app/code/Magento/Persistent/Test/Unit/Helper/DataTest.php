<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Helper;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Persistent\Helper\Data;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    protected $_modulesReader;

    /**
     * @var  Data
     */
    protected $_helper;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var string
     */
    protected $storeCode;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->_modulesReader = $this->createMock(Reader::class);
        $objectManager = new ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            Data::class,
            ['modulesReader' => $this->_modulesReader]
        );

        $this->storeCode = 'default';
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeMock->method('getCode')->willReturn($this->storeCode);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
    }

    /**
     * @param bool $isEnabled
     * @return void
     */
    protected function setUpConfigForPersistentCart(bool $isEnabled)
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->scopeConfigMock->method('isSetFlag')
            ->with(Data::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $this->storeCode)
            ->willReturn($isEnabled);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $objectManager = new ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            Data::class,
            [
                'context' => $contextMock,
                'modulesReader' => $this->_modulesReader,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testGetPersistentConfigFilePath()
    {
        $this->_modulesReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Persistent'
        )->willReturn(
            'path123'
        );
        $this->assertEquals('path123/persistent.xml', $this->_helper->getPersistentConfigFilePath());
    }

    /**
     *  Test isEnabled returns true when Persistent Cart is enabled in configuration
     */
    public function testPersistentCartCanBeEnabled()
    {
        $this->setUpConfigForPersistentCart(true);
        $this->assertTrue($this->_helper->isEnabled($this->storeCode));
    }

    /**
     *  Test isEnabled returns false when Persistent Cart is disabled in configuration
     */
    public function testPersistentCartCanBeDisabled()
    {
        $this->setUpConfigForPersistentCart(false);
        $this->assertFalse($this->_helper->isEnabled($this->storeCode));
    }

    /**
     * Test canProcess returns true when Persistent Cart is Enabled
     */
    public function testCanProcessWhenEnabled()
    {
        $this->setUpConfigForPersistentCart(true);
        $this->scopeConfigMock->expects($this->once())
            ->method(
                'isSetFlag'
            )->with(
                Data::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $this->storeCode
            )->willReturn(
                true
            );

        /** @var MockObject|Observer $eventObserverMock */
        $eventObserverMock = $this->createMock(Observer::class);
        $this->assertTrue($this->_helper->canProcess($eventObserverMock));
    }

    /**
     * Test canProcess returns false when Persistent Cart is Disabled
     */
    public function testCanProcessWhenDisabled()
    {
        $this->setUpConfigForPersistentCart(false);
        $this->scopeConfigMock->expects($this->once())
            ->method(
                'isSetFlag'
            )->with(
                Data::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $this->storeCode
            )->willReturn(
                false
            );

        /** @var MockObject|Observer $eventObserverMock */
        $eventObserverMock = $this->createMock(Observer::class);
        $this->assertFalse($this->_helper->canProcess($eventObserverMock));
    }

}
