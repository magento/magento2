<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Checkout\Block\Onepage\Payment\Methods
 */
namespace Magento\Checkout\Block\Onepage\Payment;

class MethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Onepage\Payment\Methods
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Checkout\Block\Onepage\Payment\Methods'
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetMethodTitleAndMethodLabelAfterHtml()
    {
        $expectedTitle = 'Free Method';
        $expectedLabel = 'Label After Html';
        $method = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Payment\Model\Method\Free'
        );

        $block = $this->_block->getLayout()->createBlock('Magento\Framework\View\Element\Text')
            ->setMethodTitle($expectedTitle)
            ->setMethodLabelAfterHtml($expectedLabel);

        $this->assertEquals('No Payment Information Required', $this->_block->getMethodTitle($method));
        $this->_block->setChild('payment.method.free', $block);
        $actualTitle = $this->_block->getMethodTitle($method);
        $actualLabel = $this->_block->getMethodLabelAfterHtml($method);

        $this->assertEquals($expectedTitle, $actualTitle);
        $this->assertEquals($expectedLabel, $actualLabel);
    }
}
