<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Review\Block\Form */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /**
     * @var \Magento\Review\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reviewDataMock;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepository;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\UrlInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    protected function setUp()
    {
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        $this->reviewDataMock = $this->getMockBuilder('\Magento\Review\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->reviewDataMock->expects($this->once())
            ->method('getIsGuestAllowToWrite')
            ->willReturn(true);

        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')->getMockForAbstractClass();
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->context->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->will(
            $this->returnValue($this->storeManager)
        );
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->productRepository = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\Review\Block\Form',
            [
                'context' => $this->context,
                'reviewData' => $this->reviewDataMock,
                'productRepository' => $this->productRepository,
            ]
        );
    }

    public function testGetProductInfo()
    {
        $productId = 3;
        $storeId = 1;

        $this->storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue(new \Magento\Framework\DataObject(['id' => $storeId]))
        );

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($productId);

        $productMock = $this->getMock('Magento\Catalog\Api\Data\ProductInterface');
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $this->assertSame($productMock, $this->object->getProductInfo());
    }

    /**
     * @param bool $isSecure
     * @param string $actionUrl
     * @param int $productId
     * @dataProvider getActionDataProvider
     */
    public function testGetAction($isSecure, $actionUrl, $productId)
    {
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('review/product/post', ['_secure' => $isSecure, 'id' => $productId])
            ->willReturn($actionUrl . '/id/' . $productId);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($productId);
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn($isSecure);

        $this->assertEquals($actionUrl . '/id/' . $productId, $this->object->getAction());
    }

    public function getActionDataProvider()
    {
        return [
            [false, 'http://localhost/review/product/post', 3],
            [true, 'https://localhost/review/product/post' ,3],
        ];
    }
}
