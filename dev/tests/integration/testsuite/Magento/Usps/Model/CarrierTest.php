<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use PHPUnit\Framework\TestCase;

/**
 * Test for USPS integration.
 */
class CarrierTest extends TestCase
{
    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @var AsyncClientInterfaceMock
     */
    private $httpClient;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->carrier = Bootstrap::getObjectManager()->get(Carrier::class);
        $this->httpClient = Bootstrap::getObjectManager()->get(AsyncClientInterface::class);
    }

    /**
     * Test collecting rates from the provider.
     *
     * @magentoConfigFixture default_store carriers/usps/allowed_methods 0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/userid test
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testCollectRates(): void
    {
        $requestXml = '<?xml version="1.0" encoding="UTF-8"?><RateV4Request USERID="213MAGEN6752">'
            .'<Revision>2</Revision><Package ID="0"><Service>ALL</Service><ZipOrigination>90034</ZipOrigination>'
            .'<ZipDestination>90032</ZipDestination><Pounds>4</Pounds><Ounces>4.2512000000</Ounces>'
            .'<Container>VARIABLE</Container><Size>REGULAR</Size><Machinable>true</Machinable></Package>'
            .'</RateV4Request>';
        $requestXml = (new \SimpleXMLElement($requestXml))->asXml();
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/success_usps_response_rates.xml');
        $this->httpClient->nextResponses([new Response(200, [], $responseBody)]);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country_id' => 'US',
                    'dest_region_id' => '12',
                    'dest_region_code' => 'CA',
                    'dest_street' => 'main st1',
                    'dest_city' => 'Los Angeles',
                    'dest_postcode' => '90032',
                    'package_value' => '5',
                    'package_value_with_discount' => '5',
                    'package_weight' => '4.2657',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_userid' => '213MAGEN6752',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                ]
            ]
        );

        $rates = $this->carrier->collectRates($request);
        $httpRequest = $this->httpClient->getLastRequest();
        $this->assertNotEmpty($httpRequest);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $uri = parse_url($httpRequest->getUrl(), PHP_URL_QUERY);
        $this->assertNotEmpty(preg_match('/API\=([A-z0-9]+)/', $uri, $matches));
        $apiV = $matches[1];
        unset($matches);
        $this->assertEquals('RateV4', $apiV);
        $this->assertNotEmpty(preg_match('/XML\=([^\&]+)/', $uri, $matches));
        $xml = urldecode($matches[1]);
        $this->assertEquals($requestXml, $xml);
        $this->assertNotEmpty($rates->getAllRates());
        $this->assertEquals(5.6, $rates->getAllRates()[2]->getPrice());
        $this->assertEquals(
            "Priority Mail 1-Day\nSmall Flat Rate Envelope",
            $rates->getAllRates()[2]->getMethodTitle()
        );
    }

    /**
     * Test collecting rates only for available services.
     *
     * @magentoConfigFixture default_store carriers/usps/allowed_methods 0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/userid test
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testCollectUnavailableRates(): void
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/response_rates.xml');
        $this->httpClient->nextResponses([new Response(200, [], $responseBody)]);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country_id' => 'CA',
                    'dest_postcode' => 'M5V 3G5',
                    'dest_country_name' => 'Canada',
                    'package_value' => '3.2568',
                    'package_value_with_discount' => '5',
                    'package_weight' => '5',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_userid' => '213MAGEN6752',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                ]
            ]
        );

        $rates = $this->carrier->collectRates($request);
        $this->assertCount(5, $rates->getAllRates());
    }
}
