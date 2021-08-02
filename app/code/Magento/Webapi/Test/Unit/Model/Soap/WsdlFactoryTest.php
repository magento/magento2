<?php declare(strict_types=1);
/**
 * Test \Magento\Webapi\Model\Soap\WsdlFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap;

use Magento\Framework\ObjectManagerInterface;
use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Model\Soap\WsdlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class WsdlFactoryTest
 */
class WsdlFactoryTest extends TestCase
{
    /** @var MockObject */
    protected $_objectManagerMock;

    /** @var WsdlFactory */
    protected $_soapWsdlFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_soapWsdlFactory = new WsdlFactory($this->_objectManagerMock);
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        unset($this->_objectManagerMock);
        unset($this->_soapWsdlFactory);
        parent::tearDown();
    }

    /**
     * Test create.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $wsdlName = 'wsdlName';
        $endpointUrl = 'endpointUrl';
        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(
                Wsdl::class,
                ['name' => $wsdlName, 'uri' => $endpointUrl]
            );
        $this->_soapWsdlFactory->create($wsdlName, $endpointUrl);
    }
}
