<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Framework\App\Action\Action;

/**
 * Class CompareTest
 */
class CompareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $compareHelper;

    /**
     * @var \Magento\Framework\App\Helper\Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Url | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\EncoderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Catalog\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogSessionMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlBuilder = $this->getMock('Magento\Framework\Url', ['getUrl'], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', ['getServer', 'isSecure'], [], '', false);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $this->context = $this->getMock(
            'Magento\Framework\App\Helper\Context',
            ['getUrlBuilder', 'getRequest', 'getUrlEncoder'],
            [],
            '',
            false
        );
        $this->urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')->getMock();
        $this->urlEncoder->expects($this->any())
            ->method('encode')
            ->willReturnCallback(function ($url) {
                return strtr(base64_encode($url), '+/=', '-_,');
            });
        $this->context->expects($this->once())
            ->method('getUrlBuilder')
            ->will($this->returnValue($this->urlBuilder));
        $this->context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->once())
            ->method('getUrlEncoder')
            ->will($this->returnValue($this->urlEncoder));
        $this->postDataHelper = $this->getMock(
            'Magento\Framework\Data\Helper\PostHelper',
            ['getPostData'],
            [],
            '',
            false
        );
        $this->catalogSessionMock = $this->getMock(
            '\Magento\Catalog\Model\Session',
            ['getBeforeCompareUrl'],
            [],
            '',
            false
        );

        $this->compareHelper = $objectManager->getObject(
            'Magento\Catalog\Helper\Product\Compare',
            [
                'context' => $this->context,
                'postHelper' => $this->postDataHelper,
                'catalogSession' => $this->catalogSessionMock
            ]
        );
    }

    public function testGetPostDataRemove()
    {
        //Data
        $productId = 1;
        $removeUrl = 'catalog/product_compare/remove';
        $postParams = [
            Action::PARAM_NAME_URL_ENCODED => '',
            'product' => $productId
        ];

        //Verification
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($removeUrl)
            ->will($this->returnValue($removeUrl));
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($removeUrl, $postParams)
            ->will($this->returnValue(true));

        /** @var \Magento\Catalog\Model\Product | \PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock('Magento\Catalog\Model\Product', ['getId', '__wakeup'], [], '', false);
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));

        $this->assertTrue($this->compareHelper->getPostDataRemove($product));
    }

    public function testGetClearListUrl()
    {
        //Data
        $url = 'catalog/product_compare/clear';

        //Verification
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($url)
            ->will($this->returnValue($url));

        $this->assertEquals($url, $this->compareHelper->getClearListUrl());
    }

    public function testGetPostDataClearList()
    {
        //Data
        $clearUrl = 'catalog/product_compare/clear';
        $postParams = [
            Action::PARAM_NAME_URL_ENCODED => ''
        ];

        //Verification
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with($clearUrl)
            ->will($this->returnValue($clearUrl));

        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with($clearUrl, $postParams)
            ->will($this->returnValue(true));

        $this->assertTrue($this->compareHelper->getPostDataClearList());
    }

    public function testGetAddToCartUrl()
    {
        $productId = 42;
        $isRequestSecure = false;
        $beforeCompareUrl = 'http://magento.com/compare/before';
        $encodedCompareUrl = strtr(base64_encode($beforeCompareUrl), '+/=', '-_,');
        $expectedResult = [
            'product' => $productId,
            Action::PARAM_NAME_URL_ENCODED => $encodedCompareUrl,
            '_secure' => $isRequestSecure
        ];

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->catalogSessionMock->expects($this->once())->method('getBeforeCompareUrl')->willReturn($beforeCompareUrl);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->urlEncoder->expects($this->once())->method('encode')->with($beforeCompareUrl)
            ->willReturn($encodedCompareUrl);
        $this->request->expects($this->once())->method('isSecure')->willReturn($isRequestSecure);

        $this->urlBuilder->expects($this->once())->method('getUrl')->with('checkout/cart/add', $expectedResult);
        $this->compareHelper->getAddToCartUrl($productMock);
    }
}
