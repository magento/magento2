<?php
/**
 * Test \Magento\Webapi\Model\Soap\WsdlFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

class WsdlFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Webapi\Model\Soap\WsdlFactory */
    protected $_soapWsdlFactory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_soapWsdlFactory = new \Magento\Webapi\Model\Soap\WsdlFactory($this->_objectManagerMock);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_objectManagerMock);
        unset($this->_soapWsdlFactory);
        parent::tearDown();
    }

    public function testCreate()
    {
        $wsdlName = 'wsdlName';
        $endpointUrl = 'endpointUrl';
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Webapi\Model\Soap\Wsdl::class,
            ['name' => $wsdlName, 'uri' => $endpointUrl]
        );
        $this->_soapWsdlFactory->create($wsdlName, $endpointUrl);
    }
}
