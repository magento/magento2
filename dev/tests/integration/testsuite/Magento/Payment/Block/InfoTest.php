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
namespace Magento\Payment\Block;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoConfigFixture current_store payment/banktransfer/title Bank Method Title
     * @magentoConfigFixture current_store payment/checkmo/title Checkmo Title Of The Method
     * @magentoAppArea adminhtml
     */
    public function testGetChildPdfAsArray()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $block = $layout->createBlock('Magento\Payment\Block\Info', 'block');

        /** @var $paymentInfoBank \Magento\Payment\Model\Info  */
        $paymentInfoBank = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Payment\Model\Info'
        );
        $paymentInfoBank->setMethodInstance(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\OfflinePayments\Model\Banktransfer'
            )
        );
        /** @var $childBank \Magento\Payment\Block\Info\Instructions */
        $childBank = $layout->addBlock('Magento\Payment\Block\Info\Instructions', 'child.one', 'block');
        $childBank->setInfo($paymentInfoBank);

        $nonExpectedHtml = 'non-expected html';
        $childHtml = $layout->addBlock('Magento\Framework\View\Element\Text', 'child.html', 'block');
        $childHtml->setText($nonExpectedHtml);

        /** @var $paymentInfoCheckmo \Magento\Payment\Model\Info */
        $paymentInfoCheckmo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Payment\Model\Info'
        );
        $paymentInfoCheckmo->setMethodInstance(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\OfflinePayments\Model\Checkmo'
            )
        );
        /** @var $childCheckmo \Magento\OfflinePayments\Block\Info\Checkmo */
        $childCheckmo = $layout->addBlock('Magento\OfflinePayments\Block\Info\Checkmo', 'child.just.another', 'block');
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
