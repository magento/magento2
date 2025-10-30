<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetCurrentBase64Url()
    {
        $urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $url = 'http://example.com';
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($url);
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($encodedUrl);
        $context = $this->objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );
        /** @var Data $helper */
        $helper = new Data($context);
        $this->assertEquals($encodedUrl, $helper->getCurrentBase64Url());
    }

    /**
     * @param string $url
     * @param int $callNum
     * @dataProvider getEncodedUrlDataProvider
     */
    public function testGetEncodedUrl($url, $callNum)
    {
        $urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $encodingUrl = $url ? $url : 'http://example.com';
        $urlBuilderMock->expects($this->exactly($callNum))
            ->method('getCurrentUrl')
            ->willReturn($encodingUrl);
        $encodedUrl = 'encodedUrl';
        $urlEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($encodedUrl);
        $context = $this->objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $urlBuilderMock,
                'urlEncoder' => $urlEncoder,
            ]
        );

        /** @var Data $helper */
        $helper = new Data($context);
        $this->assertEquals($encodedUrl, $helper->getEncodedUrl($url));
    }

    /**
     * @return array
     */
    public static function getEncodedUrlDataProvider()
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
    public static function addRequestParamDataProvider()
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
                ['object' => new DataObject()],
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
    public static function removeRequestParamDataProvider()
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
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject(Data::class, $arguments);
    }
}
