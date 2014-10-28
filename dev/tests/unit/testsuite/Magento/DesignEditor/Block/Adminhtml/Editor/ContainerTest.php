<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array('urlBuilder' => $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false));
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Container::setFrameUrl
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Container::getFrameUrl
     */
    public function testGetSetFrameUrl()
    {
        $arguments = array('urlBuilder' => $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false));

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
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $buttonList = $this->getMock('Magento\Backend\Block\Widget\Button\ButtonList', array(), array(), '', false);
        $arguments = $this->_getBlockArguments();
        $arguments['eventManager'] = $eventManager;
        $arguments['buttonList'] = $buttonList;


        /** @var $block \Magento\DesignEditor\Block\Adminhtml\Editor\Container */
        $block = $this->_helper->getObject('Magento\DesignEditor\Block\Adminhtml\Editor\Container', $arguments);

        $layout = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $expectedButtonData = array(
                'label' => $buttonTitle,
                'onclick' => 'setLocation(\'\')',
                'class' => 'back'
        );
        $buttonList->expects($this->once())->method('add')->with('back_button', $expectedButtonData, 0, 0, 'toolbar');
        $block->setLayout($layout);
    }
}
