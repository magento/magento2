<?php
/**
 * Test for Webapi Response model.
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Response object.
     *
     * @var \Magento\Webapi\Controller\Response
     */
    protected $_response;

    protected function setUp()
    {
        /** Initialize SUT. */
        $this->_response = new \Magento\Webapi\Controller\Response();
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_response);
        parent::tearDown();
    }

    /**
     * Test addMessage, hasMessage, getMessage, and clearMessages methods.
     */
    public function testMessagesCrud()
    {
        /** Test that new object does not contain any messages. */
        $this->assertFalse($this->_response->hasMessages(), 'New object contains messages.');

        /** Test message adding functionality. */
        $this->_response->addMessage(
            'Message text',
            \Magento\Webapi\Controller\Response::HTTP_OK,
            array('key' => 'value'),
            \Magento\Webapi\Controller\Response::MESSAGE_TYPE_SUCCESS
        );
        $this->assertTrue($this->_response->hasMessages(), 'New message is not added correctly.');

        /** Test message getting functionality. */
        $expectedMessage = array(
            \Magento\Webapi\Controller\Response::MESSAGE_TYPE_SUCCESS => array(
                array(
                    'key' => 'value',
                    'message' => 'Message text',
                    'code' => \Magento\Webapi\Controller\Response::HTTP_OK
                )
            )
        );
        $this->assertEquals($expectedMessage, $this->_response->getMessages(), 'Message is got incorrectly.');

        /** Test message clearing functionality. */
        $this->_response->clearMessages();
        $this->assertFalse($this->_response->hasMessages(), 'Message is not cleared.');
    }
}
