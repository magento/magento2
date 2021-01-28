<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetCurrentBase64Url()
    {
        $urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $url = 'http://example.com';
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($url);
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($encodedUrl);
        $context = $this->objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );
        /** @var \Magento\Framework\Url\Helper\Data $helper */
        $helper = new \Magento\Framework\Url\Helper\Data($context);
        $this->assertEquals($encodedUrl, $helper->getCurrentBase64Url());
    }

    /**
     * @param string $url
     * @param int $callNum
     * @dataProvider getEncodedUrlDataProvider
     */
    public function testGetEncodedUrl($url, $callNum)
    {
        $urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encodingUrl = $url ? $url : 'http://example.com';
        $urlBuilderMock->expects($this->exactly($callNum))
            ->method('getCurrentUrl')
            ->willReturn($encodingUrl);
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder(\Magento\Framework\Url\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($encodedUrl);
        $context = $this->objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );

        /** @var \Magento\Framework\Url\Helper\Data $helper */
        $helper = new \Magento\Framework\Url\Helper\Data($context);
        $this->assertEquals($encodedUrl, $helper->getEncodedUrl($url));
    }

    /**
     * @return array
     */
    public function getEncodedUrlDataProvider()
    {
        return [
            'no url' => [null, 1],
            'with url' => ['http://test.com', 0],
        ];
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

    /**
     * @return array
     */
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
                ['object' => new \Magento\Framework\DataObject()],
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

    /**
     * @return array
     */
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
     * @return \Magento\Framework\Url\Helper\Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject(\Magento\Framework\Url\Helper\Data::class, $arguments);
    }
}
