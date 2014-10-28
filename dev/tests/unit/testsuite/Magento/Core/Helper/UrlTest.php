<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
        ->disableOriginalConstructor()
        ->getMock();
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $url = 'http://example.com';
        $urlBuilderMock->expects($this->once())
            ->method('getCurrentUrl')
            ->will($this->returnValue($url));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'urlBuilder' => $urlBuilderMock,
            ]
        );
        /** @var \Magento\Core\Helper\Url | \PHPUnit_Framework_MockObject_MockObject $helper */
        $helper = $this->getMockBuilder('Magento\Core\Helper\Url')
            ->setConstructorArgs([$context, $storeManagerMock])
            ->setMethods(['urlEncode'])
            ->getMock();
        $encodedUrl = 'encodedUrl';
        $helper->expects($this->once())
            ->method('urlEncode')
            ->with($url)
            ->will($this->returnValue($encodedUrl));
        $this->assertEquals($encodedUrl, $helper->getCurrentBase64Url());
    }

    /**
     * @param string $url
     * @param int $callNum
     * @dataProvider getEncodedUrlDataProvider
     */
    public function testGetEncodedUrl($url, $callNum)
    {
        $storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $encodingUrl = $url ? $url : 'http://example.com';
        $urlBuilderMock->expects($this->exactly($callNum))
            ->method('getCurrentUrl')
            ->will($this->returnValue($encodingUrl));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'urlBuilder' => $urlBuilderMock,
            ]
        );
        /** @var \Magento\Core\Helper\Url | \PHPUnit_Framework_MockObject_MockObject $helper */
        $helper = $this->getMockBuilder('Magento\Core\Helper\Url')
            ->setConstructorArgs([$context, $storeManagerMock])
            ->setMethods(['urlEncode'])
            ->getMock();
        $encodedUrl = 'encodedUrl';
        $helper->expects($this->once())
            ->method('urlEncode')
            ->with($encodingUrl)
            ->will($this->returnValue($encodedUrl));
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
        $storeManagerMock = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
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
                    'array' => ['arrayVal1', 'arrayVal2', 'arrayVal3']
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
                'http://example.com?null&string=value&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3'
            ],
            'one match' => [
                'string',
                'http://example.com?null&array[]=arrayVal1&array[]=arrayVal2&array[]=arrayVal3'
            ],
            'array match' => [
                'array[]',
                'http://example.com?null&string=value'
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
