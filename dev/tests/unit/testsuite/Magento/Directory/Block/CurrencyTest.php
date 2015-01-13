<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Block;

class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Block\Currency
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    public function setUp()
    {
        $this->urlBuilder = $this->getMock(
            '\Magento\Framework\UrlInterface\Proxy',
            ['getUrl'],
            [],
            '',
            false
        );
        $this->urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));

        /** @var \Magento\Framework\View\Element\Template\Context $context */
        $context = $this->getMock(
            '\Magento\Framework\View\Element\Template\Context',
            ['getUrlBuilder'],
            [],
            '',
            false
        );
        $context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilder));

        /** @var \Magento\Directory\Model\CurrencyFactory $currencyFactory */
        $currencyFactory = $this->getMock('\Magento\Directory\Model\CurrencyFactory', [], [], '', false);
        $this->postDataHelper = $this->getMock('\Magento\Core\Helper\PostData', [], [], '', false);

        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolver */
        $localeResolver = $this->getMock('\Magento\Framework\Locale\ResolverInterface', [], [], '', false);

        $this->object = new Currency(
            $context,
            $currencyFactory,
            $this->postDataHelper,
            $localeResolver
        );
    }

    public function testGetSwitchCurrencyPostData()
    {
        $expectedResult = 'post_data';
        $expectedCurrencyCode = 'test';
        $switchUrl = 'directory/currency/switch';

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($this->equalTo($switchUrl), $this->equalTo(['currency' => $expectedCurrencyCode]))
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->object->getSwitchCurrencyPostData($expectedCurrencyCode));
    }
}
