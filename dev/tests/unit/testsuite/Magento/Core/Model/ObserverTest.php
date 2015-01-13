<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheFrontendMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontendPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeCustomization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetsMock;

    /**
     * @var \Magento\Core\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_cacheFrontendMock = $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface');

        $this->_frontendPoolMock = $this->getMock(
            'Magento\Framework\App\Cache\Frontend\Pool',
            [],
            [],
            '',
            false
        );
        $this->_frontendPoolMock->expects($this->any())->method('valid')->will($this->onConsecutiveCalls(true, false));
        $this->_frontendPoolMock->expects(
            $this->any()
        )->method(
            'current'
        )->will(
            $this->returnValue($this->_cacheFrontendMock)
        );

        $this->_themeCustomization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );
        $themeMock = $this->getMock(
            'Magento\Core\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );
        $themeMock->expects(
            $this->any()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($this->_themeCustomization)
        );

        $designMock = $this->getMock('Magento\Framework\View\DesignInterface');
        $designMock->expects($this->any())->method('getDesignTheme')->will($this->returnValue($themeMock));

        $this->_assetsMock = $this->getMock(
            'Magento\Framework\View\Asset\GroupedCollection',
            [],
            [],
            '',
            false,
            false
        );
        $this->_configMock = $this->getMock(
            '\Magento\Framework\App\Config\ReinitableConfigInterface',
            [],
            [],
            '',
            false,
            false
        );

        $this->_assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Core\Model\Observer',
            [
                'cacheFrontendPool' => $this->_frontendPoolMock,
                'design' => $designMock,
                'assets' => $this->_assetsMock,
                'assetRepo' => $this->_assetRepo,
            ]
        );
    }

    protected function tearDown()
    {
        $this->_cacheFrontendMock = null;
        $this->_frontendPoolMock = null;
        $this->_themeCustomization = null;
        $this->_assetsMock = null;
        $this->_model = null;
    }

    public function testCleanCache()
    {
        $cacheBackendMock = $this->getMockForAbstractClass('Zend_Cache_Backend_Interface');
        $cacheBackendMock->expects($this->once())->method('clean')->with(\Zend_Cache::CLEANING_MODE_OLD, []);
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'getBackend'
        )->will(
            $this->returnValue($cacheBackendMock)
        );
        $cronScheduleMock = $this->getMock('Magento\Cron\Model\Schedule', [], [], '', false);
        $this->_model->cleanCache($cronScheduleMock);
    }

    public function testApplyThemeCustomization()
    {
        $asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $file = $this->getMock('Magento\Core\Model\Theme\File', [], [], '', false);
        $fileService = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Design\Theme\Customization\FileAssetInterface'
        );
        $file->expects($this->any())->method('getCustomizationService')->will($this->returnValue($fileService));

        $this->_assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->will($this->returnValue($asset));

        $this->_themeCustomization->expects($this->once())->method('getFiles')->will($this->returnValue([$file]));
        $this->_assetsMock->expects($this->once())->method('add')->with($this->anything(), $asset);

        $observer = new \Magento\Framework\Event\Observer();
        $this->_model->applyThemeCustomization($observer);
    }
}
