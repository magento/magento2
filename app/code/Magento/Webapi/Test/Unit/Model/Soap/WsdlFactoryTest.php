<?php
/**
 * Test \Magento\Webapi\Model\Soap\WsdlFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

class WsdlFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Webapi\Model\Soap\WsdlFactory */
    protected $_soapWsdlFactory;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_soapWsdlFactory = new \Magento\Webapi\Model\Soap\WsdlFactory($this->_objectManagerMock);
        parent::setUp();
    }

    protected function tearDown(): void
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
