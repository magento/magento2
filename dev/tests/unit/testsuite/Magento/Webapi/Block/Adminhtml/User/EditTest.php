<?php
/**
 * Test class for \Magento\Webapi\Block\Adminhtml\User\Edit
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
namespace Magento\Webapi\Block\Adminhtml\User;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Webapi\Block\Adminhtml\User\Edit
     */
    protected $_block;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Magento\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('user_id')
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
        $this->_block = $helper->getObject('Magento\Webapi\Block\Adminhtml\User\Edit', array(
            // TODO: Remove injecting of 'urlBuilder' after MAGENTOTWO-5038 complete
            'urlBuilder' => $this->getMockBuilder('Magento\Backend\Model\Url')
                ->disableOriginalConstructor()
                ->getMock(),
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
        $this->assertAttributeEquals('adminhtml_user', '_controller', $this->_block);
        $this->assertAttributeEquals('user_id', '_objectId', $this->_block);
        $this->_assertBlockHasButton(1, 'save', 'label', 'Save API User');
        $this->_assertBlockHasButton(1, 'save', 'id', 'save_button');
        $this->_assertBlockHasButton(0, 'delete', 'label', 'Delete API User');
    }

    /**
     * Test getHeaderText method.
     */
    public function testGetHeaderText()
    {
        $apiUser = new \Magento\Object();
        $this->_block->setApiUser($apiUser);
        $this->assertEquals('New API User', $this->_block->getHeaderText());

        $apiUser->setId(1)->setApiKey('test-api');

        $this->_coreData->expects($this->once())
            ->method('escapeHtml')
            ->with($apiUser->getApiKey())
            ->will($this->returnArgument(0));


        $this->assertEquals("Edit API User 'test-api'", $this->_block->getHeaderText());
    }

    /**
     * Asserts that block has button with ID and attribute at level.
     *
     * @param int $level
     * @param string $buttonId
     * @param string $attributeName
     * @param string $attributeValue
     */
    protected function _assertBlockHasButton($level, $buttonId, $attributeName, $attributeValue)
    {
        $buttonsProperty = new \ReflectionProperty($this->_block, '_buttons');
        $buttonsProperty->setAccessible(true);
        $buttons = $buttonsProperty->getValue($this->_block);
        $this->assertInternalType('array', $buttons, 'Cannot get block buttons.');
        $this->assertArrayHasKey($level, $buttons, "Block doesn't have buttons at level $level");
        $this->assertArrayHasKey($buttonId, $buttons[$level], "Block doesn't have '$buttonId' button at level $level");
        $this->assertArrayHasKey($attributeName, $buttons[$level][$buttonId],
            "Block button doesn't have attribute $attributeName");
        $this->assertEquals($attributeValue, $buttons[$level][$buttonId][$attributeName],
            "Block button $attributeName' has unexpected value.");
    }
}
