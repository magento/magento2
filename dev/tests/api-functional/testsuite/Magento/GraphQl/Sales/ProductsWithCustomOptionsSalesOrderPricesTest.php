<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\GraphQl\Sales\Order\Create;

/**
 * Test for guestOrder.items.prices
 */
class ProductsWithCustomOptionsSalesOrderPricesTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 30;
    private const PRODUCT_SPECIAL_PRICE = 20;
    private const TAX_PERCENTAGE = 10;

    private const EMAIL = "guest@magento.com";
    private const LASTNAME = "test shipLast";

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @var Create
     */
    private $createOrder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->uidEncoder = $this->objectManager->create(Uid::class);
        $this->quoteIdToMaskedQuoteIdInterface = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->createOrder = $this->objectManager->get(Create::class);
    }

    /**
     * Test original_row_total with tax
     *
     * @return void
     * @throws Exception
     */
    #[
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
                'price' => self::PRODUCT_PRICE,
                'special_price' => self::PRODUCT_SPECIAL_PRICE,
                'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$'],
                'options' => [
                    [
                        'title' => 'option1',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                        'price' => 20,
                        'is_require' => false
                    ],
                    [
                        'title' => 'option2',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                        'price' => 10,
                        'is_require' => false
                    ],
                    [
                        'title' => 'option3',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                        'price' => 50,
                        'is_require' => false
                    ],
                    [
                        'title' => 'dropdown',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'is_require' => false,
                        'values' => [
                            [
                                'title' => 'option1_value1',
                                'price' => 10,
                                'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                            ]
                        ]
                    ],
                    [
                        'title' => 'multiple option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'values' => [
                            [
                                'title' => 'multiple option 1',
                                'price' => 10,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 1 sku',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2',
                                'price' => 20,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 2 sku',
                                'sort_order' => 2,
                            ],
                            [
                                'title' => 'multiple option 3',
                                'price' => 20,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 3 sku',
                                'sort_order' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => self::EMAIL])
    ]
    public function testOrderItemPricesWithTax(): void
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $product = $this->fixtures->get('product');
        $sku = $product->getSku();
        $options = $product->getOptions();

        $fieldOption1Id = $this->uidEncoder->encode(
            'custom-option' . '/' . $options[0]->getData()['option_id']
        );
        $fieldOption2Id = $this->uidEncoder->encode(
            'custom-option' . '/' . $options[1]->getData()['option_id']
        );
        $productOptions = [];
        foreach ($options[3]->getValues() as $value) {
            $productOptions[] = [
                'title' => $value->getTitle(),
                'uid' => $this->uidEncoder->encode(
                    'custom-option' . '/' . $options[3]->getData()['option_id'] . '/' . $value->getId()
                )
            ];
        }
        $dropDownOptionId = $productOptions[0]['uid'];
        $productOptions = [];
        foreach ($options[4]->getValues() as $value) {
            $productOptions[] = [
                'title' => $value->getTitle(),
                'uid' => $this->uidEncoder->encode(
                    'custom-option' . '/' . $options[4]->getData()['option_id'] . '/' . $value->getId()
                )
            ];
        }
        $multiOptionId = $productOptions[0]['uid'];
        $multiOption1Id = $productOptions[1]['uid'];
        $optionIds = json_encode([$dropDownOptionId, $multiOptionId, $multiOption1Id]);
        $selectedOptions = "selected_options: {$optionIds}";
        $this->graphQlMutation(
            $this->addProductWithOptionMutation(
                $maskedQuoteId,
                1,
                $sku,
                $fieldOption1Id,
                $fieldOption2Id,
                $selectedOptions
            )
        );
        $order = $this->createOrder->placeOrder($maskedQuoteId);
        $response = $this->graphQlQuery(
            $this->getQuery(
                $order['placeOrder']['order']['order_number'],
                self::EMAIL,
                self::LASTNAME
            )
        );
        self::assertEquals(
            [
                'original_row_total' => [
                    'value' => 86
                ],
                'original_row_total_including_tax' => [
                    'value' => 94.6
                ],
            ],
            $response['guestOrder']['items'][0]['prices']
        );
    }

    /**
     * Test original_row_total without tax
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'price' => self::PRODUCT_PRICE,
                'special_price' => self::PRODUCT_SPECIAL_PRICE,
                'options' => [
                    [
                        'title' => 'option1',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                        'price' => 20,
                        'is_require' => false
                    ],
                    [
                        'title' => 'option2',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                        'price' => 10,
                        'is_require' => false
                    ],
                    [
                        'title' => 'option3',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
                        'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                        'price' => 50,
                        'is_require' => false
                    ],
                    [
                        'title' => 'dropdown',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'is_require' => false,
                        'values' => [
                            [
                                'title' => 'option1_value1',
                                'price' => 10,
                                'price_type' => ProductPriceOptionsInterface::VALUE_PERCENT,
                            ]
                        ]
                    ],
                    [
                        'title' => 'multiple option',
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE,
                        'is_require' => false,
                        'values' => [
                            [
                                'title' => 'multiple option 1',
                                'price' => 10,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 1 sku',
                                'sort_order' => 1,
                            ],
                            [
                                'title' => 'multiple option 2',
                                'price' => 20,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 2 sku',
                                'sort_order' => 2,
                            ],
                            [
                                'title' => 'multiple option 3',
                                'price' => 20,
                                'price_type' => ProductPriceOptionsInterface::VALUE_FIXED,
                                'sku' => 'multiple option 3 sku',
                                'sort_order' => 2,
                            ],
                        ],
                    ]
                ]
            ],
            'product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => self::EMAIL])
    ]
    public function testOrderItemPricesWithOutTax(): void
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $product = $this->fixtures->get('product');
        $sku = $product->getSku();
        $options = $product->getOptions();

        $fieldOption1Id = $this->uidEncoder->encode(
            'custom-option' . '/' . $options[0]->getData()['option_id']
        );
        $fieldOption2Id = $this->uidEncoder->encode(
            'custom-option' . '/' . $options[1]->getData()['option_id']
        );
        $productOptions = [];
        foreach ($product->getOptions()[3]->getValues() as $value) {
            $productOptions[] = [
                'title' => $value->getTitle(),
                'uid' => $this->uidEncoder->encode(
                    'custom-option' . '/' . $options[3]->getData()['option_id'] . '/' . $value->getId()
                )
            ];
        }
        $dropDownOptionId = $productOptions[0]['uid'];
        $productOptions = [];
        foreach ($product->getOptions()[4]->getValues() as $value) {
            $productOptions[] = [
                'title' => $value->getTitle(),
                'uid' => $this->uidEncoder->encode(
                    'custom-option' . '/' . $options[4]->getData()['option_id'] . '/' . $value->getId()
                )
            ];
        }
        $multiOptionId = $productOptions[0]['uid'];
        $multiOption1Id = $productOptions[1]['uid'];
        $optionIds = json_encode([$dropDownOptionId, $multiOptionId, $multiOption1Id]);
        $selectedOptions = "selected_options: {$optionIds}";
        $this->graphQlMutation(
            $this->addProductWithOptionMutation(
                $maskedQuoteId,
                1,
                $sku,
                $fieldOption1Id,
                $fieldOption2Id,
                $selectedOptions
            )
        );

        $order = $this->createOrder->placeOrder($maskedQuoteId);
        $response = $this->graphQlQuery(
            $this->getQuery(
                $order['placeOrder']['order']['order_number'],
                self::EMAIL,
                self::LASTNAME
            )
        );
        self::assertEquals(
            [
                'original_row_total' => [
                    'value' => 86
                ],
                'original_row_total_including_tax' => [
                    'value' => 86
                ],
            ],
            $response['guestOrder']['items'][0]['prices']
        );
    }

    /**
     * Generates GraphQl query for retrieving guest cart prices
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
        original_row_total {
          value
        }
        original_row_total_including_tax {
          value
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Add product with custom option to the cart
     *
     * @param string $cartId
     * @param int $qty
     * @param string $sku
     * @param string $optionUid
     * @param string $optionUid2
     * @return string
     */
    private function addProductWithOptionMutation(
        string $cartId,
        int $qty,
        string $sku,
        string $fieldOption1Id,
        string $fieldOption2Id,
        string $selectedOptions = ''
    ): string {
        return <<<MUTATION
mutation {
    addProductsToCart(
        cartId: "{$cartId}"
        cartItems: [
            {
                quantity: {$qty}
                sku: "{$sku}"
                $selectedOptions
                entered_options: [
                    {
                        uid:"{$fieldOption1Id}",
                        value:"value1"
                    }
                    {
                        uid:"{$fieldOption2Id}",
                        value:"value2"
                    }
                ]
            }
        ]
    ) {
        cart {
            id
        }
    }
}
MUTATION;
    }
}
