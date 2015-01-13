<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\Theme\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
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
            'Magento\Theme\Model\Observer',
            [
                'design' => $designMock,
                'assets' => $this->_assetsMock,
                'assetRepo' => $this->_assetRepo,
            ]
        );
    }

    protected function tearDown()
    {
        $this->_themeCustomization = null;
        $this->_assetsMock = null;
        $this->_model = null;
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
