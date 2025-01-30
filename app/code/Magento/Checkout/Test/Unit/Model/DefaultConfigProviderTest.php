<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Cart\ImageProvider;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country\Postcode\ConfigInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Escaper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultConfigProviderTest extends TestCase
{
    /**
     * @var DefaultConfigProvider
     */
    private $model;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSession;

    /**
     * @var ShippingMethodManager|MockObject
     */
    private $shippingMethodManager;

    /**
     * @var AddressMetadataInterface|MockObject
     */
    private $addressMetadata;

    /**
     * @var CartTotalRepositoryInterface|MockObject
     */
    private $cartTotalRepository;

    /**
     * @var Config|MockObject
     */
    private $shippingMethodConfig;

    /**
     * @var CaptchaConfigPostProcessorInterface|MockObject
     */
    private $configPostProcessor;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $checkoutHelper = $this->createMock(CheckoutHelper::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $customerRepository = $this->createMock(CustomerRepository::class);
        $customerSession = $this->createMock(CustomerSession::class);
        $customerUrlManager = $this->createMock(CustomerUrlManager::class);
        $httpContext = $this->createMock(HttpContext::class);
        $quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $quoteItemRepository = $this->createMock(QuoteItemRepository::class);
        $this->shippingMethodManager = $this->getMockBuilder(ShippingMethodManager::class)
            ->addMethods(['get'])
            ->getMockForAbstractClass();
        $configurationPool = $this->createMock(ConfigurationPool::class);
        $quoteIdMaskFactory = $this->createMock(QuoteIdMaskFactory::class);
        $localeFormat = $this->createMock(LocaleFormat::class);
        $addressMapper = $this->createMock(Mapper::class);
        $addressConfig = $this->createMock(\Magento\Customer\Model\Address\Config::class);
        $formKey = $this->createMock(FormKey::class);
        $imageHelper = $this->createMock(Image::class);
        $viewConfig = $this->createMock(\Magento\Framework\View\ConfigInterface::class);
        $postCodesConfig = $this->createMock(ConfigInterface::class);
        $imageProvider = $this->createMock(ImageProvider::class);
        $directoryHelper = $this->createMock(Data::class);
        $this->cartTotalRepository = $this->createMock(CartTotalRepositoryInterface::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->shippingMethodConfig = $this->createMock(Config::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $paymentMethodManagement = $this->createMock(PaymentMethodManagementInterface::class);
        $urlBuilder = $this->createMock(UrlInterface::class);
        $this->configPostProcessor = $this->createMock(CaptchaConfigPostProcessorInterface::class);
        $this->addressMetadata = $this->createMock(AddressMetadataInterface::class);
        $attributeOptionManager = $this->createMock(AttributeOptionManagementInterface::class);
        $customerAddressData = $this->createMock(CustomerAddressDataProvider::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->model = new DefaultConfigProvider(
            $checkoutHelper,
            $this->checkoutSession,
            $customerRepository,
            $customerSession,
            $customerUrlManager,
            $httpContext,
            $quoteRepository,
            $quoteItemRepository,
            $this->shippingMethodManager,
            $configurationPool,
            $quoteIdMaskFactory,
            $localeFormat,
            $addressMapper,
            $addressConfig,
            $formKey,
            $imageHelper,
            $viewConfig,
            $postCodesConfig,
            $imageProvider,
            $directoryHelper,
            $this->cartTotalRepository,
            $scopeConfig,
            $this->shippingMethodConfig,
            $storeManager,
            $paymentMethodManagement,
            $urlBuilder,
            $this->configPostProcessor,
            $this->addressMetadata,
            $attributeOptionManager,
            $customerAddressData,
            $this->escaper
        );
    }

    /**
     * @param array $shippingAddressData
     * @param array $billingAddressData
     * @param array $expected
     * @dataProvider getConfigQuoteAddressDataDataProvider
     */
    public function testGetConfigQuoteAddressData(
        array $shippingAddressData,
        array $billingAddressData,
        array $expected
    ): void {
        $shippingAddressData['email'] = 'john.doe@example.com';
        $billingAddressData['email'] = 'john.doe@example.com';
        $keys = [
            'isShippingAddressFromDataValid',
            'shippingAddressFromData',
            'isBillingAddressFromDataValid',
            'billingAddressFromData',
        ];
        $quote = $this->createMock(Quote::class);
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMockForAbstractClass();
        $shippingAddress->addData($shippingAddressData);
        $shippingAddress->method('validate')
            ->willReturn(!empty($shippingAddress['firstname']));
        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMockForAbstractClass();
        $billingAddress->addData($billingAddressData);
        $billingAddress->method('validate')
            ->willReturn(!empty($shippingAddress['firstname']));
        $quote->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $quote->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->method('getStore')
            ->willReturn($this->createMock(Store::class));
        $this->checkoutSession->expects($this->atLeast(1))
            ->method('getQuote')
            ->willReturn($quote);

        $attributeMetadata1 = $this->createMock(AttributeMetadataInterface::class);
        $attributeMetadata1->method('isVisible')
            ->willReturn(true);
        $attributeMetadata1->method('getAttributeCode')
            ->willReturn('firstname');

        $attributeMetadata2 = $this->createMock(AttributeMetadataInterface::class);
        $attributeMetadata2->method('isVisible')
            ->willReturn(true);
        $attributeMetadata2->method('getAttributeCode')
            ->willReturn('lastname');

        $this->addressMetadata->method('getAllAttributesMetadata')
            ->willReturn([$attributeMetadata1, $attributeMetadata2]);

        $totals = $this->getMockBuilder(TotalsInterface::class)
            ->addMethods(['toArray'])
            ->getMockForAbstractClass();
        $totals->method('getItems')
            ->willReturn([]);
        $totals->method('getTotalSegments')
            ->willReturn([]);
        $this->cartTotalRepository->method('get')
            ->willReturn($totals);
        $this->shippingMethodConfig->method('getActiveCarriers')
            ->willReturn([]);
        $this->configPostProcessor->method('process')
            ->willReturnArgument(0);
        $actual = array_intersect_key($this->model->getConfig(), array_flip($keys));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getConfigQuoteAddressDataDataProvider(): array
    {
        return [
            [
                [],
                [],
                []
            ],
            [
                [
                    'firstname' => 'John'
                ],
                [
                    'firstname' => 'Jack'
                ],
                [
                    'isShippingAddressFromDataValid' => true,
                    'shippingAddressFromData' => [
                        'firstname' => 'John'
                    ],
                    'isBillingAddressFromDataValid' => true,
                    'billingAddressFromData' => [
                        'firstname' => 'Jack'
                    ]
                ]
            ],
            [
                [
                    'lastname' => 'John'
                ],
                [
                    'lastname' => 'Jack'
                ],
                [
                    'isShippingAddressFromDataValid' => false,
                    'shippingAddressFromData' => [
                        'lastname' => 'John'
                    ],
                    'isBillingAddressFromDataValid' => false,
                    'billingAddressFromData' => [
                        'lastname' => 'Jack'
                    ]
                ]
            ],
            [
                [
                    'firstname' => 'John'
                ],
                [
                    'firstname' => 'John'
                ],
                [
                    'isShippingAddressFromDataValid' => true,
                    'shippingAddressFromData' => [
                        'firstname' => 'John'
                    ],
                ]
            ],
        ];
    }
}
