<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Verify FedEx International Priority shipping method is available on guest checkout for a non-US address.
 *
 * Integration replacement for MFTF test StorefrontVerifyFedExInternationalPriorityShippingMethodIsAvailableTest
 * (ACP2E-3667). The FedEx REST API is mocked so the result no longer depends on the live FedEx sandbox.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InternationalPriorityShippingMethodIsAvailableTest extends TestCase
{
    private const CARRIER_CODE = 'fedex';
    private const METHOD_CODE = 'FEDEX_INTERNATIONAL_PRIORITY';
    private const OAUTH_END_POINT = 'oauth/token';
    private const RATE_END_POINT = 'rate/v1/rates/quotes';

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var string[]
     */
    private array $rateRequests = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->addSharedInstance($this->createFedexCurlFactoryMock(), CurlFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(CurlFactory::class);
        parent::tearDown();
    }

    /**
     * Guest with a Canada shipping address sees FedEx International Priority when it is the only allowed method.
     *
     * @return void
     */
    #[
        Config('carriers/fedex/active', '1', 'store', 'default'),
        Config('carriers/fedex/sandbox_mode', '1', 'store', 'default'),
        Config('carriers/fedex/debug', '1', 'store', 'default'),
        Config('carriers/fedex/showmethod', '1', 'store', 'default'),
        Config('carriers/fedex/account', 'test_account', 'store', 'default'),
        Config('carriers/fedex/smartpost_hubid', '55312', 'store', 'default'),
        Config('carriers/fedex/api_key', 'test_api_key', 'store', 'default'),
        Config('carriers/fedex/secret_key', 'test_secret_key', 'store', 'default'),
        Config('carriers/fedex/allowed_methods', 'FEDEX_INTERNATIONAL_PRIORITY', 'store', 'default'),
        DataFixture(ProductFixture::class, ['price' => 123, 'weight' => 1], 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1])
    ]
    public function testInternationalPriorityShippingMethodIsAvailableForCanadaAddress(): void
    {
        $cartId = (int)DataFixtureStorageManager::getStorage()->get('cart')->getId();
        $maskedCartId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class)->execute($cartId);

        // Guest shipping address the MFTF test fills in (Simple_US_Customer + Canada_Address).
        $region = $this->objectManager->get(RegionFactory::class)->create()->loadByName('Saskatchewan', 'CA');
        $address = $this->objectManager->get(AddressInterfaceFactory::class)->create()
            ->setEmail('John.Doe@example.com')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setStreet(['4423  St. John Street'])
            ->setCity('Alameda')
            ->setCountryId('CA')
            ->setRegionId((int)$region->getId())
            ->setRegion('Saskatchewan')
            ->setPostcode('S4P3Y2')
            ->setTelephone('613-582-4782');

        $methods = $this->objectManager->get(GuestShipmentEstimationInterface::class)
            ->estimateByExtendedAddress($maskedCartId, $address);

        $this->assertCount(1, $this->rateRequests, 'FedEx rate request was not sent.');
        $rateRequest = json_decode($this->rateRequests[0], true);
        $this->assertSame(
            'CA',
            $rateRequest['requestedShipment']['recipient']['address']['countryCode'] ?? null,
            'FedEx rate request was not sent for the Canada shipping address.'
        );

        $fedexMethod = null;
        foreach ($methods as $method) {
            if ($method->getCarrierCode() === self::CARRIER_CODE && $method->getMethodCode() === self::METHOD_CODE) {
                $fedexMethod = $method;
            }
        }
        $this->assertNotNull(
            $fedexMethod,
            'FedEx International Priority shipping method is not displayed for the non-US shipping address.'
        );
        $this->assertTrue($fedexMethod->getAvailable(), 'FedEx International Priority shipping method is unavailable.');
        $this->assertEmpty((string)$fedexMethod->getErrorMessage());
        $this->assertSame('International Priority', (string)$fedexMethod->getMethodTitle());
    }

    /**
     * Create a CurlFactory stub that answers FedEx OAuth and rate requests with recorded API responses.
     *
     * @return CurlFactory
     */
    private function createFedexCurlFactoryMock(): CurlFactory
    {
        $lastUrl = '';
        $curlClient = $this->createStub(Curl::class);
        $curlClient->method('post')->willReturnCallback(
            function (string $url, $request) use (&$lastUrl): void {
                $lastUrl = $url;
                if (str_contains($url, self::RATE_END_POINT)) {
                    $this->rateRequests[] = (string)$request;
                }
            }
        );
        $curlClient->method('getBody')->willReturnCallback(
            function () use (&$lastUrl): string {
                if (str_contains($lastUrl, self::OAUTH_END_POINT)) {
                    return $this->loadResponse('mock_rest_response_auth.json');
                }
                if (str_contains($lastUrl, self::RATE_END_POINT)) {
                    return $this->loadResponse('mock_rest_response_general_ca.json');
                }
                return '{}';
            }
        );

        $curlFactory = $this->createStub(CurlFactory::class);
        $curlFactory->method('create')->willReturn($curlClient);

        return $curlFactory;
    }

    /**
     * Load a recorded FedEx REST API response.
     *
     * @param string $fileName
     * @return string
     */
    private function loadResponse(string $fileName): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return (string)file_get_contents(__DIR__ . '/../_files/' . $fileName);
    }
}
