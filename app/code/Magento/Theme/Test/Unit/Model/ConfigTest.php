<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme config model
 */
namespace Magento\Theme\Test\Unit\Model;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_themeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configData;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configCacheMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_layoutCacheMock;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_scopeConfigWriter;

    /**
     * @var \Magento\Theme\Model\Config
     */
    protected $_model;

    protected function setUp(): void
    {
        /** @var $this->_themeMock \Magento\Theme\Model\Theme */
        $this->_themeMock = $this->createMock(\Magento\Theme\Model\Theme::class);
        $this->_storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStores', 'isSingleStoreMode']
        );
        $this->_configData = $this->createPartialMock(
            \Magento\Framework\App\Config\Value::class,
            ['getCollection', 'addFieldToFilter', '__wakeup']
        );
        $this->_configCacheMock = $this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class);
        $this->_layoutCacheMock = $this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class);

        $this->_scopeConfigWriter = $this->createPartialMock(
            \Magento\Framework\App\Config\Storage\WriterInterface::class,
            ['save', 'delete']
        );

        $this->_model = new \Magento\Theme\Model\Config(
            $this->_configData,
            $this->_scopeConfigWriter,
            $this->_storeManagerMock,
            $this->createMock(\Magento\Framework\Event\ManagerInterface::class),
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
        $configEntity = new \Magento\Framework\DataObject(['value' => 6, 'scope_id' => 8]);

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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(2)
        )->method(
            'addFieldToFilter'
        )->with(
            'path',
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID
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
        $configEntity = new \Magento\Framework\DataObject(['value' => 6, 'scope_id' => 8]);

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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        )->willReturn(
            $this->_configData
        );

        $this->_configData->expects(
            $this->at(2)
        )->method(
            'addFieldToFilter'
        )->with(
            'path',
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID
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
