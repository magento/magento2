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

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLogicExceptionEmptyRequestAdapter()
    {
        $this->setExpectedException('LogicException', 'Request deserializer adapter is not set.');
        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $this->getMock('Magento\Framework\ObjectManager'),
            array()
        );
        $interpreterFactory->get('contentType');
    }

    public function testGet()
    {
        $expectedMetadata = array('text_xml' => array('type' => 'text/xml', 'model' => 'Xml'));
        $validInterpreterMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request\Deserializer\Xml'
        )->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($validInterpreterMock));

        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }

    public function testGetMagentoWebapiException()
    {
        $expectedMetadata = array('text_xml' => array('type' => 'text/xml', 'model' => 'Xml'));
        $this->setExpectedException(
            'Magento\Webapi\Exception',
            'Server cannot understand Content-Type HTTP header media type text_xml'
        );
        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $this->getMock('Magento\Framework\ObjectManager'),
            $expectedMetadata
        );
        $interpreterFactory->get('text_xml');
    }

    public function testGetLogicExceptionInvalidRequestDeserializer()
    {
        $expectedMetadata = array('text_xml' => array('type' => 'text/xml', 'model' => 'Xml'));
        $invalidInterpreter = $this->getMockBuilder(
            'Magento\Webapi\Controller\Response\Rest\Renderer\Json'
        )->disableOriginalConstructor()->getMock();

        $this->setExpectedException(
            'LogicException',
            'The deserializer must implement "Magento\Webapi\Controller\Rest\Request\DeserializerInterface".'
        );
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($invalidInterpreter));

        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }
}
