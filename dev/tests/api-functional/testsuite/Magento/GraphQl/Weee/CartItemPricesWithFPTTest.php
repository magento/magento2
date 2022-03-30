<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Weee;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for cart item fixed product tax
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CartItemPricesWithFPTTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager $objectManager
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $initialConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);

        $currentSettingsArray = [
            'tax/display/type',
            'tax/weee/enable',
            'tax/weee/display',
            'tax/defaults/region',
            'tax/weee/apply_vat',
            'tax/calculation/price_includes_tax'
        ];

        foreach ($currentSettingsArray as $configPath) {
            $this->initialConfig[$configPath] = $this->scopeConfig->getValue(
                $configPath
            );
        }
        /** @var ReinitableConfigInterface $config */
        $config = $this->objectManager->get(ReinitableConfigInterface::class);
        $config->reinit();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->writeConfig($this->initialConfig);
    }

    /**
     * Write configuration for weee
     *
     * @param array $settings
     * @return void
     */
    private function writeConfig(array $settings): void
    {
        /** @var WriterInterface $configWriter */
        $configWriter = $this->objectManager->get(WriterInterface::class);

        foreach ($settings as $path => $value) {
            $configWriter->save($path, $value);
        }
        $this->scopeConfig->clean();
    }

    /**
     * @param array $taxSettings
     * @param array $expectedFtps
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider cartItemFixedProductTaxDataProvider
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/Weee/_files/product_with_two_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Weee/_files/add_fpt_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Weee/_files/apply_tax_for_simple_product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Weee/_files/add_simple_product_with_fpt_to_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testCartItemFixedProductTax(array $taxSettings, array $expectedFtps): void
    {
        $this->writeConfig($taxSettings);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['cart']['items']);
        $actualFtps = $result['cart']['items'][0]['prices']['fixed_product_taxes'];
        $this->assertEqualsCanonicalizing($expectedFtps, $actualFtps);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function cartItemFixedProductTaxDataProvider(): array
    {
        return [
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '0',
                    'tax/calculation/price_includes_tax' => '0',
                    'tax/display/type' => '1',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.0
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 12.7
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.0
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '0',
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '1',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.0
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 12.7
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.0
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '1',
                    'tax/calculation/price_includes_tax' => '0',
                    'tax/display/type' => '1',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.0
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 12.7
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.0
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '1',
                    'tax/calculation/price_includes_tax' => '0',
                    'tax/display/type' => '2',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.75
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 13.66
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.38
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '1',
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '1',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.0
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 12.7
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.01
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '1',
                    'tax/weee/apply_vat' => '1',
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '2',
                ],
                'expectedFtps' => [
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 10.75
                        ]
                    ],
                    [
                        'label' => 'fpt_for_all_front_label',
                        'amount' => [
                            'value' => 13.65
                        ]
                    ],
                    [
                        'label' => 'fixed_product_attribute_front_label',
                        'amount' => [
                            'value' => 5.38
                        ]
                    ],
                ]
            ],
            [
                'taxSettings' => [
                    'tax/weee/enable' => '0',
                    'tax/weee/apply_vat' => '1',
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '1',
                ],
                'expectedFtps' => []
            ]
        ];
    }

    /**
     * Generates GraphQl query for retrieving cart totals
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    items {
      prices {
        price {
          value
          currency
        }
        row_total {
          value
          currency
        }
        row_total_including_tax {
          value
          currency
        }
        fixed_product_taxes {
          label
          amount {
            value
          }
        }
      }
    }
    prices {
      grand_total {
        value
        currency
      }
      subtotal_including_tax {
        value
        currency
      }
      subtotal_excluding_tax {
        value
        currency
      }
      subtotal_with_discount_excluding_tax {
        value
        currency
      }
      applied_taxes {
        label
        amount {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }
}
