<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Helper;

use PHPUnit\Framework\TestCase;
use Magento\OfflinePayments\Block\Info\Checkmo;
use Magento\Payment\Model\Info;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Payment\Helper\Data
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = Bootstrap::getObjectManager()->get(Data::class);
    }

    /**
     * @return void
     */
    public function testGetInfoBlock()
    {
        $paymentInfo = Bootstrap::getObjectManager()->create(
            Info::class
        );
        $paymentInfo->setMethod('checkmo');
        $result = $this->helper->getInfoBlock($paymentInfo);
        $this->assertInstanceOf(Checkmo::class, $result);
    }

    /**
     * Test to load Payment method title from store config
     *
     * @magentoConfigFixture current_store payment/cashondelivery/title Cash On Delivery Title Of The Method
     */
    public function testPaymentMethodLabelByStore()
    {
        $result = $this->helper->getPaymentMethodList(true, true);
        $this->assertArrayHasKey('cashondelivery', $result, 'Payment info does not exist');
        $this->assertEquals(
            'Cash On Delivery Title Of The Method',
            $result['cashondelivery']['label'],
            'Payment method title is not loaded from store config'
        );
    }
}
