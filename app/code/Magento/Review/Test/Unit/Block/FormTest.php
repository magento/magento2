<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block;

use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\ZendEscaper;
use Magento\Review\Model\Rating;

class FormTest extends \PHPUnit\Framework\TestCase
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

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializerMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->reviewDataMock = $this->getMockBuilder(\Magento\Review\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reviewDataMock->expects($this->once())
            ->method('getIsGuestAllowToWrite')
            ->willReturn(true);

        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->context->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->will(
            $this->returnValue($this->storeManager)
        );
        $escaper = new Escaper();
        $zendEscaper = new ZendEscaper();
        $this->objectManagerHelper->setBackwardCompatibleProperty($escaper, 'escaper', $zendEscaper);
        $this->context->expects($this->any())
            ->method('getEscaper')
            ->willReturn($escaper);
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)->getMock();

        $this->object = $this->objectManagerHelper->getObject(
            \Magento\Review\Block\Form::class,
            [
                'context' => $this->context,
                'reviewData' => $this->reviewDataMock,
                'productRepository' => $this->productRepository,
                'data' => [
                    'jsLayout' => [
                        'some-layout' => 'layout information'
                    ]
                ],
                'serializer' => $this->serializerMock
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

        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
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

    public function testGetJsLayout()
    {
        $jsLayout = [
            'some-layout' => 'layout information'
        ];

        $this->serializerMock->expects($this->once())->method('serialize')
            ->will($this->returnValue(json_encode($jsLayout)));
        $this->assertEquals('{"some-layout":"layout information"}', $this->object->getJsLayout());
    }

    /**
     * Test getCode() will replace spaces with underscores in order to build proper unique element id.
     *
     * @dataProvider getCodeDataProvider
     * @param string $expectedCode
     * @param string $code
     * @return void
     */
    public function testGetCode(string $expectedCode, string $code)
    {
        /** @var Rating|\PHPUnit_Framework_MockObject_MockObject $rating */
        $rating = $this->getMockBuilder(Rating::class)
            ->setMethods(['getRatingCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $rating->expects($this->once())
            ->method('getRatingCode')
            ->willReturn($code);

        $this->assertSame($expectedCode, $this->object->getCode($rating));
    }

    /**
     * Provide test data for testGetCode().
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return [
            [
                'expected_code' => 'test',
                'code' => 'test',
            ],
            [
                'expected_code' => 'test_code_with_spaces',
                'code' => 'test code with spaces',
            ],
            [
                'expected_code' => '&lt;p&gt;test_code_with_spaces_and_tags&lt;&#x2F;p&gt;',
                'code' => '<p>test code with spaces and tags</p>'
            ]
        ];
    }
}
