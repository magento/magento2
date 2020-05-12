<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme config model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_themeMock;

    /**
     * @var MockObject
     */
    protected $_configData;

    /**
     * @var MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var MockObject
     */
    protected $_configCacheMock;

    /**
     * @var MockObject
     */
    protected $_layoutCacheMock;

    /**
     * @var WriterInterface
     */
    protected $_scopeConfigWriter;

    /**
     * @var Config
     */
    protected $_model;

    protected function setUp(): void
    {
        /** @var $this->_themeMock \Magento\Theme\Model\Theme */
        $this->_themeMock = $this->createMock(Theme::class);
        $this->_storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStores', 'isSingleStoreMode']
        );
        $this->_configData = $this->getMockBuilder(Value::class)
            ->addMethods(['addFieldToFilter'])
            ->onlyMethods(['getCollection', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_configCacheMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->_layoutCacheMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $this->_scopeConfigWriter = $this->createPartialMock(
            WriterInterface::class,
            ['save', 'delete']
        );

        $this->_model = new Config(
            $this->_configData,
            $this->_scopeConfigWriter,
            $this->_storeManagerMock,
            $this->getMockForAbstractClass(ManagerInterface::class),
            $this->_configCacheMock,
            $this->_layoutCacheMock
        );
    }

    protected function tearDown(): void
    {
        $this->_themeMock = null;
        $this->_configData = null;
        $this->_themeFactoryMock = null;
        $this->_configCacheMock = null;
        $this->_layoutCacheMock = null;
        $this->_model = null;
    }

    /**
     * cover \Magento\Theme\Model\Config::assignToStore
     */
    public function testAssignToStoreInSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(true);

        $themePath = 'Magento/blank';
        /** Unassign themes from store */
        $configEntity = new DataObject(['value' => 6, 'scope_id' => 8]);

        $this->_configData->expects(
            $this->once()
        )->method(
            'getCollection'
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(1)
        )->method(
            'addFieldToFilter'
        )->with(
            'scope',
            ScopeInterface::SCOPE_STORES
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(2)
        )->method(
            'addFieldToFilter'
        )->with(
            'path',
            DesignInterface::XML_PATH_THEME_ID
        )->willReturn(
            [$configEntity]
        );

        $this->_themeMock->expects($this->any())->method('getId')->willReturn(6);
        $this->_themeMock->expects($this->any())->method('getThemePath')->willReturn($themePath);

        $this->_scopeConfigWriter->expects($this->once())->method('delete');

        $this->_scopeConfigWriter->expects($this->once())->method('save');

        $this->_model->assignToStore($this->_themeMock, [2, 3, 5]);
    }

    /**
     * cover \Magento\Theme\Model\Config::assignToStore
     */
    public function testAssignToStoreNonSingleStoreMode()
    {
        $this->_storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(false);

        $themePath = 'Magento/blank';
        /** Unassign themes from store */
        $configEntity = new DataObject(['value' => 6, 'scope_id' => 8]);

        $this->_configData->expects(
            $this->once()
        )->method(
            'getCollection'
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(1)
        )->method(
            'addFieldToFilter'
        )->with(
            'scope',
            ScopeInterface::SCOPE_STORES
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(2)
        )->method(
            'addFieldToFilter'
        )->with(
            'path',
            DesignInterface::XML_PATH_THEME_ID
        )->willReturn(
            [$configEntity]
        );

        $this->_themeMock->expects($this->any())->method('getId')->willReturn(6);
        $this->_themeMock->expects($this->any())->method('getThemePath')->willReturn($themePath);

        $this->_scopeConfigWriter->expects($this->once())->method('delete');

        $this->_scopeConfigWriter->expects($this->exactly(3))->method('save');

        $this->_model->assignToStore($this->_themeMock, [2, 3, 5]);
    }
}
