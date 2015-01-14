<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

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
     * @var \Magento\Core\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->urlBuilder = $this->getMock('Magento\Framework\Url', ['getUrl'], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', ['getServer'], [], '', false);
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
            ->will($this->returnArgument(0));
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
            'Magento\Core\Helper\PostData',
            ['getPostData'],
            [],
            '',
            false
        );

        $this->compareHelper = $objectManager->getObject(
            'Magento\Catalog\Helper\Product\Compare',
            ['context' => $this->context, 'coreHelper' => $this->postDataHelper]
        );
    }

    public function testGetPostDataRemove()
    {
        //Data
        $productId = 1;
        $removeUrl = 'catalog/product_compare/remove';
        $compareListUrl = 'catalog/product_compare';
        $postParams = [
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $compareListUrl,
            'product' => $productId
        ];

        //Verification
        $this->urlBuilder->expects($this->at(0))
            ->method('getUrl')
            ->with($compareListUrl)
            ->will($this->returnValue($compareListUrl));
        $this->urlBuilder->expects($this->at(1))
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
        $refererUrl = 'home/';
        $clearUrl = 'catalog/product_compare/clear';
        $postParams = [
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $refererUrl
        ];

        //Verification
        $this->request->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->will($this->returnValue($refererUrl));

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
}
