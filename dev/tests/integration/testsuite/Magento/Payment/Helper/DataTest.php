<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Helper\Data
 */
namespace Magento\Payment\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
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
}
