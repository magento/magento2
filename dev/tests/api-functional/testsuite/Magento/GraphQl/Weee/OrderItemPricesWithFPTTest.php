<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Weee;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Weee\Test\Fixture\Attribute as FptAttributeFixture;

/**
 * Test for guestOrder.items.prices.fixed_product_taxes
 */
class OrderItemPricesWithFPTTest extends GraphQlAbstract
{
    private const P1_FPT_PRICE = 0.3;
    private const P2_FPT_PRICE = 0.8;
    private const TAX_PERCENTAGE = 10;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $settings = [
            'tax/weee/enable' => 1,
            'tax/weee/apply_vat' => 1,
        ];
        self::writeConfig($settings);
    }

    public static function tearDownAfterClass(): void
    {
        $settings = [
            'tax/weee/enable' => 0,
            'tax/weee/apply_vat' => 0,
        ];
        self::writeConfig($settings);
        parent::tearDownAfterClass();
    }

    /**
     * Write configuration for weee
     *
     * @param array $settings
     * @return void
     */
    public static function writeConfig(array $settings): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var ReinitableConfigInterface $config */
        $config = $objectManager->get(ReinitableConfigInterface::class);
        $config->reinit();

        /** @var WriterInterface $configWriter */
        $configWriter = $objectManager->get(WriterInterface::class);

        foreach ($settings as $path => $value) {
            $configWriter->save($path, $value);
        }

        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();
    }

    /**
     * Test order items Fixed Product Tax
     *
     * @param int $taxDisplayType
     * @param array $expectedResponse
     * @return void
     * @throws Exception
     *
     * @dataProvider orderItemFixedProductTaxDataProvider
     */
    #[
        DataFixture(FptAttributeFixture::class, ['attribute_code' => 'fpt_attr']),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, ['rate' => self::TAX_PERCENTAGE], 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => self::P1_FPT_PRICE]],
                'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'fpt_attr' => [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => self::P2_FPT_PRICE]],
                'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
            ],
            'product2'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderItemFixedProductTax(int $taxDisplayType, array $expectedResponse): void
    {
        $settings = [
            'tax/display/type' => $taxDisplayType,
        ];
        self::writeConfig($settings);

        $order = $this->fixtures->get('order');
        $response = $this->graphQlQuery(
            $this->getQuery(
                $order->getIncrementId(),
                $order->getBillingAddress()->getEmail(),
                $order->getBillingAddress()->getLastname()
            )
        );

        self::assertEquals($expectedResponse, $response);
    }

    /**
     * Data provider for checking fixed_product_taxes with and without tax display type
     *
     * @return array[]
     */
    public static function orderItemFixedProductTaxDataProvider(): array
    {
        return [
            'without_tax' => [
                'taxDisplayType' => 1,
                'expectedResponse' => [
                    'guestOrder' => [
                        'items' => [
                            0 => [
                                'prices' => [
                                    'fixed_product_taxes' => [
                                        0 => [
                                            'amount' => [
                                                'value' => self::P1_FPT_PRICE
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            1 => [
                                'prices' => [
                                    'fixed_product_taxes' => [
                                        0 => [
                                            'amount' => [
                                                'value' => self::P2_FPT_PRICE
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'with_tax' => [
                'taxDisplayType' => 2,
                'expectedResponse' => [
                    'guestOrder' => [
                        'items' => [
                            0 => [
                                'prices' => [
                                    'fixed_product_taxes' => [
                                        0 => [
                                            'amount' => [
                                                'value' => 0.33
                                                //self::P2_FPT_PRICE(0.3) with self::TAX_PERCENTAGE(10) percentage tax
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            1 => [
                                'prices' => [
                                    'fixed_product_taxes' => [
                                        0 => [
                                            'amount' => [
                                                'value' => 0.88
                                                //self::P2_FPT_PRICE(0.8) with self::TAX_PERCENTAGE(10) percentage tax
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generates GraphQl query for retrieving guest cart FPT
     *
     * @param string $number
     * @param string $email
     * @param string $lastname
     * @return string
     */
    private function getQuery(string $number, string $email, string $lastname): string
    {
        return <<<QUERY
{
  guestOrder(input: {
      number: "{$number}",
      email: "{$email}",
      lastname: "{$lastname}"
  }) {
    items {
      prices {
        fixed_product_taxes {
          amount {
            value
          }
        }
      }
    }
  }
}
QUERY;
    }
}
