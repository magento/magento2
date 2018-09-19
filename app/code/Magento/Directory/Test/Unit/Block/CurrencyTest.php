<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Block;

class CurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Block\Currency
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')->will($this->returnArgument(0));

        /**
         * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilderMock));

        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock->method('escapeUrl')
            ->willReturnCallback(
                function ($string) {
                    return 'escapeUrl' . $string;
                }
            );
        $contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaperMock);

        /** @var \Magento\Directory\Model\CurrencyFactory $currencyFactoryMock */
        $currencyFactoryMock = $this->createMock(\Magento\Directory\Model\CurrencyFactory::class);
        $this->postDataHelperMock = $this->createMock(\Magento\Framework\Data\Helper\PostHelper::class);

        /** @var \Magento\Framework\Locale\ResolverInterface $localeResolverMock */
        $localeResolverMock = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

        $this->object = new \Magento\Directory\Block\Currency(
            $contextMock,
            $currencyFactoryMock,
            $this->postDataHelperMock,
            $localeResolverMock
        );
    }

    public function testGetSwitchCurrencyPostData()
    {
        $expectedResult = 'post_data';
        $expectedCurrencyCode = 'test';
        $switchUrl = 'escapeUrldirectory/currency/switch';

        $this->postDataHelperMock->expects($this->once())
            ->method('getPostData')
            ->with($this->equalTo($switchUrl), $this->equalTo(['currency' => $expectedCurrencyCode]))
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->object->getSwitchCurrencyPostData($expectedCurrencyCode));
    }
}
