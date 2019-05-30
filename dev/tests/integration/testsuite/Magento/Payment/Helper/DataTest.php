<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Helper\Data
 */
namespace Magento\Payment\Helper;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Payment\Helper\Data class.
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    public function testGetInfoBlock()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Payment\Helper\Data::class);
        $paymentInfo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Payment\Model\Info::class
        );
        $paymentInfo->setMethod('checkmo');
        $result = $helper->getInfoBlock($paymentInfo);
        $this->assertInstanceOf(\Magento\OfflinePayments\Block\Info\Checkmo::class, $result);
    }

    /**
     * Checking if payment available in getPaymentMethodList() with set active to 0.
     *
     * @magentoConfigFixture default_store payment/checkmo/active 0
     */
    public function testGetPaymentMethodListWithInactivePayments()
    {
        /** @var \Magento\Payment\Helper\Data $helper */
        $helper = Bootstrap::getObjectManager()->get(\Magento\Payment\Helper\Data::class);
        $paymentMethodsList =  $helper->getPaymentMethodList();
        $this->assertArrayHasKey('checkmo', $paymentMethodsList);
    }

    /**
     * Checking different title for payment method.
     *
     * @magentoConfigFixture default_store payment/checkmo/title New Title
     */
    public function testGetPaymentMethodListWithDifferentTitles()
    {
        /** @var \Magento\Payment\Helper\Data $helper */
        $helper = Bootstrap::getObjectManager()->get(\Magento\Payment\Helper\Data::class);
        $storedTitle = $helper->getMethodInstance('checkmo')->getConfigData('title', 'default');
        $paymentMethodList = $helper->getPaymentMethodList();
        $this->assertEquals($storedTitle, $paymentMethodList['checkmo']);
    }
}
