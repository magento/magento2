<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block;

use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Payment\Block\Info as BlockInfo;
use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Info;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class InfoTest
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests payment info block.
     *
     * @magentoConfigFixture current_store payment/banktransfer/title Bank Method Title
     * @magentoConfigFixture current_store payment/checkmo/title Checkmo Title Of The Method
     * @magentoAppArea adminhtml
     */
    public function testGetChildPdfAsArray()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $block = $layout->createBlock(BlockInfo::class, 'block');

        /** @var $paymentInfoBank Info  */
        $paymentInfoBank = Bootstrap::getObjectManager()->create(
            Info::class
        );
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $banktransferPayment = Bootstrap::getObjectManager()->create(Banktransfer::class);
        $paymentInfoBank->setMethodInstance($banktransferPayment);
        $paymentInfoBank->setOrder($order);
        /** @var $childBank Instructions */
        $childBank = $layout->addBlock(Instructions::class, 'child.one', 'block');
        $childBank->setInfo($paymentInfoBank);

        $nonExpectedHtml = 'non-expected html';
        $childHtml = $layout->addBlock(Text::class, 'child.html', 'block');
        $childHtml->setText($nonExpectedHtml);

        /** @var $paymentInfoCheckmo Info */
        $paymentInfoCheckmo = Bootstrap::getObjectManager()->create(
            Info::class
        );
        $checkmoPayment = Bootstrap::getObjectManager()->create(Checkmo::class);
        $paymentInfoCheckmo->setMethodInstance($checkmoPayment);
        $paymentInfoCheckmo->setOrder($order);
        /** @var $childCheckmo \Magento\OfflinePayments\Block\Info\Checkmo */
        $childCheckmo = $layout->addBlock(
            \Magento\OfflinePayments\Block\Info\Checkmo::class,
            'child.just.another',
            'block'
        );
        $childCheckmo->setInfo($paymentInfoCheckmo);

        $pdfArray = $block->getChildPdfAsArray();

        $this->assertInternalType('array', $pdfArray);
        $this->assertCount(2, $pdfArray);
        $text = implode('', $pdfArray);
        $this->assertContains('Bank Method Title', $text);
        $this->assertContains('Checkmo Title Of The Method', $text);
        $this->assertNotContains($nonExpectedHtml, $text);
    }
}
