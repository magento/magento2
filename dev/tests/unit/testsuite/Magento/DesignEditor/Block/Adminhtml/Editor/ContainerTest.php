<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    const FRAME_URL = 'controller/action';

    /**
     * Object manager helper
     *
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    protected function tearDown()
    {
        unset($this->_helper);
    }

    /**
     * Retrieve list of arguments for block that will be tested
     *
     * @return array
     */
    protected function _getBlockArguments()
    {
        return ['urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false)];
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Container::setFrameUrl
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Container::getFrameUrl
     */
    public function testGetSetFrameUrl()
    {
        $arguments = ['urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false)];

        /** @var $block \Magento\DesignEditor\Block\Adminhtml\Editor\Container */
        $block = $this->_helper->getObject('Magento\DesignEditor\Block\Adminhtml\Editor\Container', $arguments);
        $block->setFrameUrl(self::FRAME_URL);
        $this->assertAttributeEquals(self::FRAME_URL, '_frameUrl', $block);
        $this->assertEquals(self::FRAME_URL, $block->getFrameUrl());
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Container::_prepareLayout
     */
    public function testPrepareLayout()
    {
        $buttonTitle = 'Back';
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $buttonList = $this->getMock('Magento\Backend\Block\Widget\Button\ButtonList', [], [], '', false);
        $arguments = $this->_getBlockArguments();
        $arguments['eventManager'] = $eventManager;
        $arguments['buttonList'] = $buttonList;

        /** @var $block \Magento\DesignEditor\Block\Adminhtml\Editor\Container */
        $block = $this->_helper->getObject('Magento\DesignEditor\Block\Adminhtml\Editor\Container', $arguments);

        $layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $expectedButtonData = [
                'label' => $buttonTitle,
                'onclick' => 'setLocation(\'\')',
                'class' => 'back',
        ];
        $buttonList->expects($this->once())->method('add')->with('back_button', $expectedButtonData, 0, 0, 'toolbar');
        $block->setLayout($layout);
    }
}
