<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Checkout;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;

use Magento\Ui\Component\Form\AttributeMapper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * LayoutProcessorTest covers a list of variations for checkout layout processor
 */
class LayoutProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeMetadataDataProvider|MockObject
     */
    private $attributeDataProvider;

    /**
     * @var AttributeMapper|MockObject
     */
    private $attributeMapper;

    /**
     * @var AttributeMerger|MockObject
     */
    private $attributeMerger;

    /**
     * @var Data|MockObject
     */
    private $dataHelper;

    /**
     * @var LayoutProcessor
     */
    private $layoutProcessor;

    /**
     * @var MockObject
     */
    private $storeManager;

    protected function setUp()
    {
        $this->attributeDataProvider = $this->getMockBuilder(AttributeMetadataDataProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAttributesCollection'])
            ->getMock();

        $this->attributeMapper = $this->getMockBuilder(AttributeMapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['map'])
            ->getMock();

        $this->attributeMerger = $this->getMockBuilder(AttributeMerger::class)
            ->disableOriginalConstructor()
            ->setMethods(['merge'])
            ->getMock();

        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDisplayBillingOnPaymentMethodAvailable'])
            ->getMock();

        $options = $this->getMockBuilder(Options::class)
            ->disableOriginalConstructor()
            ->getMock();

        $shippingConfig = $this->getMockBuilder(\Magento\Shipping\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->layoutProcessor = new LayoutProcessor(
            $this->attributeDataProvider,
            $this->attributeMapper,
            $this->attributeMerger,
            $options,
            $this->dataHelper,
            $shippingConfig,
            $this->storeManager
        );
    }

    /**
     * @covers \Magento\Checkout\Block\Checkout\LayoutProcessor::process
     */
    public function testProcess()
    {
        $jsLayout = $this->getLayoutData();

        $this->attributeDataProvider->expects(static::once())
            ->method('loadAttributesCollection')
            ->willReturn([]);

        $this->dataHelper->expects(static::once())
            ->method('isDisplayBillingOnPaymentMethodAvailable')
            ->willReturn(true);

        $this->attributeMerger->expects(static::exactly(2))
            ->method('merge')
            ->willReturnMap([
                ['payment1_1' => $this->getBillingComponent('payment1_1')],
                ['payment2_1' => $this->getBillingComponent('payment2_1')],
            ]);

        $actual = $this->layoutProcessor->process($jsLayout);

        static::assertArrayHasKey(
            'payment1_1-form',
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        );
        static::assertArrayHasKey(
            'payment2_1-form',
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        );
        static::assertArrayNotHasKey(
            'payment2_2-form',
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        );
        static::assertArrayHasKey(
            'afterMethods',
            $actual['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']
        );
        static::assertEmpty(
            $actual['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['afterMethods']['children']
        );
    }

    /**
     * @covers \Magento\Checkout\Block\Checkout\LayoutProcessor::process
     */
    public function testProcessWithBillingAddressOnPaymentPage()
    {
        $jsLayout = $this->getLayoutData();

        $this->attributeDataProvider->expects(static::once())
            ->method('loadAttributesCollection')
            ->willReturn([]);

        $this->dataHelper->expects(static::once())
            ->method('isDisplayBillingOnPaymentMethodAvailable')
            ->willReturn(false);

        $this->attributeMerger->expects(static::once())
            ->method('merge')
            ->willReturn($this->getBillingComponent('shared'));

        $actual = $this->layoutProcessor->process($jsLayout);

        static::assertEmpty(
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        );

        static::assertNotEmpty(
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']
        );
        static::assertArrayHasKey(
            'billing-address-form',
            $actual['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
            ['children']['afterMethods']['children']
        );
    }

    /**
     * Get mock layout data for testing
     * @return array
     */
    private function getLayoutData()
    {
        return [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                'billing-step' => [
                                    'children' => [
                                        'payment' => [
                                            'children' => [
                                                'renders' => [
                                                    'children' => [
                                                        'payment1' => [
                                                            'methods' => [
                                                                'payment1_1' => [
                                                                    'isBillingAddressRequired' => true
                                                                ]
                                                            ]
                                                        ],
                                                        'payment2' => [
                                                            'methods' => [
                                                                'payment2_1' => [
                                                                    'isBillingAddressRequired' => true
                                                                ],
                                                                'payment2_2' => [
                                                                    'isBillingAddressRequired' => false
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
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
     * Get mock data for billing component
     * @param string $paymentCode
     * @return array
     */
    private function getBillingComponent($paymentCode)
    {
        return [
            'country_id' => [
                'sortOrder' => 115,
            ],
            'region' => [
                'visible' => false,
            ],
            'region_id' => [
                'component' => 'Magento_Ui/js/form/element/region',
                'config' => [
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'customEntry' => 'billingAddress' . $paymentCode . '.region',
                ],
                'validation' => [
                    'required-entry' => true,
                ],
                'filterBy' => [
                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                    'field' => 'country_id',
                ],
            ],
            'postcode' => [
                'component' => 'Magento_Ui/js/form/element/post-code',
                'validation' => [
                    'required-entry' => true,
                ],
            ],
            'company' => [
                'validation' => [
                    'min_text_length' => 0,
                ],
            ],
            'fax' => [
                'validation' => [
                    'min_text_length' => 0,
                ],
            ],
            'telephone' => [
                'config' => [
                    'tooltip' => [
                        'description' => ('For delivery questions.'),
                    ],
                ],
            ],
        ];
    }
}
