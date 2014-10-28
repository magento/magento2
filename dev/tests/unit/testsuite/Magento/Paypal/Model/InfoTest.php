<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Paypal\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
            'Magento\Paypal\Model\Info'
        );
    }

    /**
     * @dataProvider additionalInfoDataProvider
     * @param array $additionalInfo
     * @param array $expectation
     */
    public function testGetPaymentInfo($additionalInfo, $expectation)
    {
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
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
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
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
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
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
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
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
        $from = new \Magento\Framework\Object($mapping);
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
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
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
        $paymentInfo->setAdditionalInformation($expectation);

        // we create $to empty object
        $to = new \Magento\Framework\Object();
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
        /** @var \Magento\Payment\Model\Info $paymentInfo */
        $paymentInfo = $this->objectManagerHelper->getObject('Magento\Payment\Model\Info');
        $paymentInfo->setAdditionalInformation($expectation);

        // we create $to empty object
        $to = new \Magento\Framework\Object();
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
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE_CNPJ
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
                ]
            ],
            [
                [
                    Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE
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
                    Info::CVV2_MATCH => Info::PAYPAL_CVV2_MATCH,
                    Info::CENTINEL_VPAS => Info::CENTINEL_VPAS,
                    Info::CENTINEL_ECI => Info::CENTINEL_ECI,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
                    Info::PAYMENT_STATUS => Info::PAYMENT_STATUS_GLOBAL,
                    Info::PENDING_REASON => Info::PENDING_REASON_GLOBAL,
                    Info::IS_FRAUD => Info::IS_FRAUD_GLOBAL
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
                    Info::PAYPAL_CVV2_MATCH => Info::PAYPAL_CVV2_MATCH,
                    Info::CENTINEL_VPAS => Info::CENTINEL_VPAS,
                    Info::CENTINEL_ECI => Info::CENTINEL_ECI,
                    Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
                    Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
                    Info::PAYMENT_STATUS_GLOBAL => Info::PAYMENT_STATUS_GLOBAL,
                    Info::PENDING_REASON_GLOBAL => Info::PENDING_REASON_GLOBAL,
                    Info::IS_FRAUD_GLOBAL => Info::IS_FRAUD_GLOBAL
                ]
            ]
        ];
    }

}
