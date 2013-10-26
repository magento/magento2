<?php
/**
 * Test class for \Magento\Webapi\Block\Adminhtml\Role\Edit
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Block\Adminhtml\Role;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Backend\Model\Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Webapi\Block\Adminhtml\Role\Edit
     */
    protected $_block;

    protected function setUp()
    {
        $this->_urlBuilder = $this->getMockBuilder('Magento\Backend\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_request = $this->getMockBuilder('Magento\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('role_id')
            ->will($this->returnValue(1));

        $this->_coreData = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('escapeHtml'))
            ->getMock();

        $helperFactory = $this->getMockBuilder('Magento\Core\Model\Factory\Helper')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();

        $helperFactory->expects($this->any())
            ->method('get')
            ->with($this->equalTo('Magento\Core\Helper\Data'))
            ->will($this->returnValue($this->_coreData));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $helper->getObject('Magento\Webapi\Block\Adminhtml\Role\Edit', array(
            'urlBuilder' => $this->_urlBuilder,
            'request' => $this->_request,
            'helperFactory' => $helperFactory,
        ));
    }

    /**
     * Test _construct method.
     */
    public function testConstruct()
    {
        $this->assertAttributeEquals('Magento_Webapi', '_blockGroup', $this->_block);
        $this->assertAttributeEquals('adminhtml_role', '_controller', $this->_block);
        $this->assertAttributeEquals('role_id', '_objectId', $this->_block);
        $this->_assertBlockHasButton(1, 'save', 'Save API Role');
        $this->_assertBlockHasButton(0, 'delete', 'Delete API Role');
    }

    /**
     * Test getSaveAndContinueUrl method.
     */
    public function testGetSaveAndContinueUrl()
    {
        $expectedUrl = 'save_and_continue_url';
        $this->_urlBuilder
            ->expects($this->once())
            ->method('getUrl')
            ->with('*/*/save', array('_current' => true, 'continue' => true))
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_block->getSaveAndContinueUrl());
    }

    /**
     * Test getHeaderText method.
     */
    public function testGetHeaderText()
    {
        $apiRole = new \Magento\Object();
        $this->_block->setApiRole($apiRole);
        $this->assertEquals('New API Role', $this->_block->getHeaderText());

        $apiRole->setId(1)->setRoleName('Test Role');

        $this->_coreData->expects($this->once())
            ->method('escapeHtml')
            ->with($apiRole->getRoleName())
            ->will($this->returnArgument(0));

        $this->assertEquals("Edit API Role 'Test Role'", $this->_block->getHeaderText());
    }

    /**
     * Asserts that block has button with ID and label at level.
     *
     * @param int $level
     * @param string $buttonId
     * @param string $label
     */
    protected function _assertBlockHasButton($level, $buttonId, $label)
    {
        $buttonsProperty = new \ReflectionProperty($this->_block, '_buttons');
        $buttonsProperty->setAccessible(true);
        $buttons = $buttonsProperty->getValue($this->_block);
        $this->assertInternalType('array', $buttons, 'Cannot get block buttons.');
        $this->assertArrayHasKey($level, $buttons, "Block doesn't have buttons at level $level");
        $this->assertArrayHasKey($buttonId, $buttons[$level], "Block doesn't have '$buttonId' button at level $level");
        $this->assertArrayHasKey('label', $buttons[$level][$buttonId], "Block button doesn't have label.");
        $this->assertEquals($label, $buttons[$level][$buttonId]['label'], "Block button label has unexpected value.");
    }
}
