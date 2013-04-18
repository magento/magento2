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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Block_Adminhtml_Editor_ContainerTest extends PHPUnit_Framework_TestCase
{
    const FRAME_URL = 'controller/action';

    /**
     * Object manager helper
     *
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new Magento_Test_Helper_ObjectManager($this);
    }

    protected function tearDown()
    {
        unset($this->_helper);
    }

    /**
     * Retrieve list of arguments for block that will be tested
     *
     * @param array $params
     * @return array
     */
    protected function _getBlockArguments(array $params)
    {
        $helper = $this->getMock('Mage_DesignEditor_Helper_Data', array('__'), array(), '', false);
        $helper->expects($this->once())
            ->method('__')
            ->with($params['expectedTranslation'])
            ->will($this->returnValue($params['expectedTranslation']));

        $helperFactory = $this->getMock('Mage_Core_Model_Factory_Helper', array('get'));
        $helperFactory->expects($this->once())
            ->method('get')
            ->will($this->returnValue($helper));

        return array(
            'urlBuilder'    => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false),
            'helperFactory' => $helperFactory
        );
    }

    public function testGetHeaderText()
    {
        $arguments = $this->_getBlockArguments(array('expectedTranslation' => 'Visual Design Editor'));
        /** @var $block Mage_DesignEditor_Block_Adminhtml_Editor_Container */
        $block = $this->_helper->getObject('Mage_DesignEditor_Block_Adminhtml_Editor_Container', $arguments);
        $block->getHeaderText();
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Container::setFrameUrl
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Container::getFrameUrl
     */
    public function testGetSetFrameUrl()
    {
        $arguments = array(
            'urlBuilder' => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false),
        );

        /** @var $block Mage_DesignEditor_Block_Adminhtml_Editor_Container */
        $block = $this->_helper->getObject('Mage_DesignEditor_Block_Adminhtml_Editor_Container', $arguments);
        $block->setFrameUrl(self::FRAME_URL);
        $this->assertAttributeEquals(self::FRAME_URL, '_frameUrl', $block);
        $this->assertEquals(self::FRAME_URL, $block->getFrameUrl());
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Container::_prepareLayout
     */
    public function testPrepareLayout()
    {
        $buttonTitle = 'Back';
        $eventManager = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $arguments = $this->_getBlockArguments(array('expectedTranslation' => $buttonTitle));
        $arguments['eventManager'] = $eventManager;

        /** @var $block Mage_DesignEditor_Block_Adminhtml_Editor_Container */
        $block = $this->_helper->getObject('Mage_DesignEditor_Block_Adminhtml_Editor_Container', $arguments);

        $layout = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $block->setLayout($layout);

        $expectedButtonData = array(
            'back_button'    => array(
                'label'      => $buttonTitle,
                'onclick'    => 'setLocation(\'\')',
                'class'      => 'back',
                'id'         => 'back_button',
                'region'     => 'header',
                'sort_order' => 10
            )
        );

        $this->assertAttributeContains($expectedButtonData, '_buttons', $block);
    }
}
