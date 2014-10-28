<?php
/**
 * Test Webapi Json Deserializer Request Rest Controller.
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
namespace Magento\Webapi\Controller\Rest\Request\Deserializer;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var \Magento\Webapi\Controller\Rest\Request\Deserializer\Json */
    protected $_jsonDeserializer;

    /** @var \Magento\Core\Helper\Data */
    protected $_helperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appStateMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperMock = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        /** Initialize SUT. */
        $this->_jsonDeserializer = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Json(
            $this->_helperMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_jsonDeserializer);
        unset($this->_helperMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    public function testDeserializerInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', '"boolean" data type is invalid. String is expected.');
        $this->_jsonDeserializer->deserialize(false);
    }

    public function testDeserialize()
    {
        /** Prepare mocks for SUT constructor. */
        $inputEncodedJson = '{"key1":"test1","key2":"test2","array":{"test01":"some1","test02":"some2"}}';
        $expectedDecodedJson = array(
            'key1' => 'test1',
            'key2' => 'test2',
            'array' => array('test01' => 'some1', 'test02' => 'some2')
        );
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonDecode'
        )->will(
            $this->returnValue($expectedDecodedJson)
        );
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedDecodedJson,
            $this->_jsonDeserializer->deserialize($inputEncodedJson),
            'Deserialization from JSON to array is invalid.'
        );
    }

    public function testDeserializeInvalidEncodedBodyExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperMock->expects($this->once())
            ->method('jsonDecode')
            ->will($this->throwException(new \Zend_Json_Exception));
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('production'));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals('Decoding error.', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    public function testDeserializeInvalidEncodedBodyExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonDecode'
        )->will(
            $this->throwException(
                new \Zend_Json_Exception('Decoding error:' . PHP_EOL . 'Decoding failed: Syntax error')
            )
        );
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('developer'));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertContains('Decoding error:', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }
}
