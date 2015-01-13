<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetCurrentBase64Url()
    {
        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
        ->disableOriginalConstructor()
        ->getMock();
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $url = 'http://example.com';
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url));
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->will($this->returnValue($encodedUrl));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );
        /** @var \Magento\Core\Helper\Url $helper */
        $helper = new Url($context, $storeManagerMock);
        $this->assertEquals($encodedUrl, $helper->getCurrentBase64Url());
    }

    /**
     * @param string $url
     * @param int $callNum
     * @dataProvider getEncodedUrlDataProvider
     */
    public function testGetEncodedUrl($url, $callNum)
    {
        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $encodingUrl = $url ? $url : 'http://example.com';
        $urlBuilderMock->expects($this->exactly($callNum))
            ->method('getCurrentUrl')
            ->will($this->returnValue($encodingUrl));
        $encodedUrl = 'encodedUrl';
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder('Magento\Framework\Url\EncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->will($this->returnValue($encodedUrl));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );

        /** @var \Magento\Core\Helper\Url $helper */
        $helper = new Url($context, $storeManagerMock);
        $this->assertEquals($encodedUrl, $helper->getEncodedUrl($url));
    }

    public function getEncodedUrlDataProvider()
    {
        return [
            'no url' => [null, 1],
            'with url' => ['http://test.com', 0],
        ];
    }

    public function testGetHomeUrl()
    {
        $storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $baseUrl = 'baseUrl';
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue($baseUrl));
        $helper = $this->getHelper(['storeManager' => $storeManagerMock]);
        $this->assertEquals($baseUrl, $helper->getHomeUrl());
    }

    /**
     * @param array $param
     * @param string $expected
     * @dataProvider addRequestParamDataProvider
     */
    public function testAddRequestParam($param, $expected)
    {
        $helper = $this->getHelper([]);
        $this->assertEquals($expected, $helper->addRequestParam('http://example.com', $param));
    }

    public function addRequestParamDataProvider()
    {
        return [
            'string' => [
                ['key1' => 'value1', 'key2' => 'value2'],
                'http://example.com?key1=value1&key2=value2',
            ],
            'numeric key' => [
                ['1' => 'value1', '2' => 'value2'],
                'http://example.com',
            ],
            'single param' => [
                ['key1' => 'value1'],
                'http://example.com?key1=value1',
            ],
            'valid/invalid param' => [
                ['1' => 'value1', 'key2' => 'value2'],
                'http://example.com?key2=value2',
            ],
            'mixed' => [
                [
                    'null' => null,
                    'string' => 'value',
                    'array' => ['arrayVal1', 'arrayVal2', 'arrayVal3'],
                ],
                'http://example.com?null&string=value&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3',
            ],
            'object' => [
                ['object' => new \Magento\Framework\Object()],
                'http://example.com',
            ]
        ];
    }

    /**
     * @param string $paramKey
     * @param string $expected
     * @dataProvider removeRequestParamDataProvider
     */
    public function testRemoveRequestParam($paramKey, $expected)
    {
        $url = 'http://example.com?null&string=value&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3';

        $helper = $this->getHelper([]);
        $this->assertEquals($expected, $helper->removeRequestParam($url, $paramKey));
    }

    public function removeRequestParamDataProvider()
    {
        return [
            'no match' => [
                'other',
                'http://example.com?null&string=value&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3',
            ],
            'one match' => [
                'string',
                'http://example.com?null&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3',
            ],
            'array match' => [
                'array[]',
                'http://example.com?null&string=value',
            ],
        ];
    }

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Url
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject('Magento\Core\Helper\Url', $arguments);
    }
}
