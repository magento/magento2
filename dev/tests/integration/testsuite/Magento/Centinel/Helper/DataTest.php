<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\Centinel\Helper\Data
 */
namespace Magento\Centinel\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInfoBlock()
    {
        /** @var $block \Magento\Payment\Helper\Data */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Payment\Helper\Data');
        /** @var $paymentInfo \Magento\Payment\Model\Info */
        $paymentInfo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Payment\Model\Info'
        );
        $paymentInfo->setMethod('checkmo');
        $result = $block->getInfoBlock($paymentInfo);
        $this->assertInstanceOf('Magento\OfflinePayments\Block\Info\Checkmo', $result);
    }
}
