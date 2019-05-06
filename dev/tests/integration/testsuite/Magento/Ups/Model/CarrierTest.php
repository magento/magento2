<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;

/**
 * UPS carrier test.
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->carrier = Bootstrap::getObjectManager()->create(Carrier::class);
    }

    /**
     * @return void
     */
    public function testGetShipAcceptUrl()
    {
        $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     * @return void
     */
    public function testGetShipAcceptUrlLive()
    {
        $this->assertEquals('https://onlinetools.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    /**
     * @return void
     */
    public function testGetShipConfirmUrl()
    {
        $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipConfirm', $this->carrier->getShipConfirmUrl());
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     * @return void
     */
    public function testGetShipConfirmUrlLive()
    {
        $this->assertEquals(
            'https://onlinetools.ups.com/ups.app/xml/ShipConfirm',
            $this->carrier->getShipConfirmUrl()
        );
    }

    /**
     * @magentoConfigFixture current_store carriers/ups/active 1
     * @magentoConfigFixture current_store carriers/ups/allowed_methods 1DA,GND
     * @magentoConfigFixture current_store carriers/ups/free_method GND
     * @magentoConfigFixture current_store carriers/ups/type UPS
     */
    public function testCollectFreeRates()
    {
        $rateRequest = Bootstrap::getObjectManager()->get(RateRequestFactory::class)->create();
        $rateRequest->setDestCountryId('US');
        $rateRequest->setDestRegionId('CA');
        $rateRequest->setDestPostcode('90001');
        $rateRequest->setPackageQty(1);
        $rateRequest->setPackageWeight(1);
        $rateRequest->setFreeMethodWeight(0);
        $rateRequest->setLimitCarrier(Carrier::CODE);
        $rateRequest->setFreeShipping(true);

        $rateResult = $this->carrier->collectRates($rateRequest);
        $result = $rateResult->asArray();
        $methods = $result[Carrier::CODE]['methods'];
        $this->assertEquals(0, $methods['GND']['price']);
        $this->assertNotEquals(0, $methods['1DA']['price']);
    }

    /**
     * Check default UPS carrier parameters.
     *
     * @return void
     */
    public function testValidDefaultParameters()
    {
        $protocolType = $this->carrier->getConfigData('type');
        $this->assertEquals("UPS_XML", $protocolType, "Default type should be UPS_XML");

        $gatewayUrl = $this->carrier->getConfigData('gateway_url');
        $this->assertEquals(
            "https://www.ups.com/using/services/rave/qcostcgi.cgi",
            $gatewayUrl,
            "Incorrect gateway url"
        );

        $gatewayXmlUrl = $this->carrier->getConfigData('gateway_xml_url');
        $this->assertEquals(
            "https://onlinetools.ups.com/ups.app/xml/Rate",
            $gatewayXmlUrl,
            "Incorrect gateway XML url"
        );
    }
}
