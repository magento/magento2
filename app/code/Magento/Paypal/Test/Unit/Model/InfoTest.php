<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\Info;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Model\Info */
    protected $info;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $this->objectManagerHelper->getObject(
            \Magento\Paypal\Model\Info::class
        );
    }

    /**
     * @dataProvider additionalInfoDataProvider
     * @param array $additionalInfo
     * @param array $expectation
     */
    public function testGetPaymentInfo($additionalInfo, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($additionalInfo);
        $this->assertEquals($expectation, $this->info->getPaymentInfo($paymentInfo));
    }

    /**
     * @dataProvider additionalInfoDataProvider
     * @param array $additionalInfo
     * @param array $expectation
     */
    public function testGetPaymentInfoLabelValues($additionalInfo, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($additionalInfo);
        $this->assertEquals(
            $this->_prepareLabelValuesExpectation($expectation),
            $this->info->getPaymentInfo($paymentInfo, true)
        );
    }

    /**
     * @dataProvider additionalInfoPublicDataProvider
     * @param array $additionalInfo
     * @param array $expectation
     */
    public function testGetPublicPaymentInfo($additionalInfo, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($additionalInfo);
        $this->assertEquals(
            $this->_prepareLabelValuesExpectation($expectation),
            $this->info->getPublicPaymentInfo($paymentInfo, true)
        );
    }

    /**
     * @dataProvider additionalInfoPublicDataProvider
     * @param array $additionalInfo
     * @param array $expectation
     */
    public function testGetPublicPaymentInfoLabelValues($additionalInfo, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($additionalInfo);
        $this->assertEquals($expectation, $this->info->getPublicPaymentInfo($paymentInfo));
    }

    /**
     * @dataProvider importToPaymentDataProvider
     * @param array $mapping
     * @param array $expectation
     */
    public function testImportToPayment($mapping, $expectation)
    {
        // we create $from object, based on mapping
        $from = new \Magento\Framework\DataObject($mapping);
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $this->info->importToPayment($from, $paymentInfo);
        $this->assertEquals($expectation, $paymentInfo->getAdditionalInformation());
    }

    /**
     * @dataProvider importToPaymentDataProvider
     * @param array $mapping
     * @param array $expectation
     */
    public function testExportFromPayment($mapping, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($expectation);

        // we create $to empty object
        $to = new \Magento\Framework\DataObject();
        $this->info->exportFromPayment($paymentInfo, $to);
        $this->assertEquals($mapping, $to->getData());
    }

    /**
     * @dataProvider importToPaymentDataProvider
     * @param array $mapping
     * @param array $expectation
     */
    public function testExportFromPaymentCustomMapping($mapping, $expectation)
    {
        /** @var \Magento\Payment\Model\InfoInterface $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject(\Magento\Payment\Model\Info::class);
        $paymentInfo->setAdditionalInformation($expectation);

        // we create $to empty object
        $to = new \Magento\Framework\DataObject();
        $this->info->exportFromPayment($paymentInfo, $to, array_flip($mapping));
        $this->assertEquals($mapping, $to->getData());
    }

    /**
     * Converts expectation result from ['key' => ['label' => 'Label', 'value' => 'Value']] to ['Label' => 'Value']
     *
     * @param $expectation
     * @return array
     */
    private function _prepareLabelValuesExpectation($expectation)
    {
        $labelValueExpectation = [];
        foreach ($expectation as $data) {
            $labelValueExpectation[$data['label']] = $data['value'];
        }
        return $labelValueExpectation;
    }

    /**
     * List of Labels
     *
     * @return array
     */
    public function additionalInfoDataProvider()
    {
        return include __DIR__ . '/_files/additional_info_data.php';
    }

    /**
     *List of public labels
     *
     * @return array
     */
    public function additionalInfoPublicDataProvider()
    {
        return [
            [
                [
                    Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE_CNPJ,
                ],
                [
                    Info::PAYPAL_PAYER_EMAIL => [
                        'label' => 'Payer Email',
                        'value' => Info::PAYPAL_PAYER_EMAIL,
                    ],
                    Info::BUYER_TAX_ID => [
                        'label' => 'Buyer\'s Tax ID',
                        'value' => Info::BUYER_TAX_ID,
                    ],
                    Info::BUYER_TAX_ID_TYPE => [
                        'label' => 'Buyer\'s Tax ID Type',
                        'value' => 'CNPJ',
                    ]
                ],
            ],
            [
                [
                    Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
                ],
                [
                    Info::PAYPAL_PAYER_EMAIL => [
                        'label' => 'Payer Email',
                        'value' => Info::PAYPAL_PAYER_EMAIL,
                    ],
                    Info::BUYER_TAX_ID => [
                        'label' => 'Buyer\'s Tax ID',
                        'value' => Info::BUYER_TAX_ID,
                    ]
                ]
            ]
        ];
    }

    /**
     * Mapping and expectation
     *
     * @return array
     */
    public function importToPaymentDataProvider()
    {
        return [
            [
                [
                    Info::PAYER_ID => Info::PAYPAL_PAYER_ID,
                    Info::PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
                    Info::PAYER_STATUS => Info::PAYPAL_PAYER_STATUS,
                    Info::ADDRESS_ID => Info::PAYPAL_ADDRESS_ID,
                    Info::ADDRESS_STATUS => Info::PAYPAL_ADDRESS_STATUS,
                    Info::PROTECTION_EL => Info::PAYPAL_PROTECTION_ELIGIBILITY,
                    Info::FRAUD_FILTERS => Info::PAYPAL_FRAUD_FILTERS,
                    Info::CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
                    Info::AVS_CODE => Info::PAYPAL_AVS_CODE,
                    Info::CVV_2_MATCH => Info::PAYPAL_CVV_2_MATCH,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
                    Info::PAYMENT_STATUS => Info::PAYMENT_STATUS_GLOBAL,
                    Info::PENDING_REASON => Info::PENDING_REASON_GLOBAL,
                    Info::IS_FRAUD => Info::IS_FRAUD_GLOBAL,
                ],
                [
                    Info::PAYPAL_PAYER_ID => Info::PAYPAL_PAYER_ID,
                    Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
                    Info::PAYPAL_PAYER_STATUS => Info::PAYPAL_PAYER_STATUS,
                    Info::PAYPAL_ADDRESS_ID => Info::PAYPAL_ADDRESS_ID,
                    Info::PAYPAL_ADDRESS_STATUS => Info::PAYPAL_ADDRESS_STATUS,
                    Info::PAYPAL_PROTECTION_ELIGIBILITY => Info::PAYPAL_PROTECTION_ELIGIBILITY,
                    Info::PAYPAL_FRAUD_FILTERS => Info::PAYPAL_FRAUD_FILTERS,
                    Info::PAYPAL_CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
                    Info::PAYPAL_AVS_CODE => Info::PAYPAL_AVS_CODE,
                    Info::PAYPAL_CVV_2_MATCH => Info::PAYPAL_CVV_2_MATCH,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
                    Info::PAYMENT_STATUS_GLOBAL => Info::PAYMENT_STATUS_GLOBAL,
                    Info::PENDING_REASON_GLOBAL => Info::PENDING_REASON_GLOBAL,
                    Info::IS_FRAUD_GLOBAL => Info::IS_FRAUD_GLOBAL
                ],
            ]
        ];
    }
}
