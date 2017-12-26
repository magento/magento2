<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\ContentProvider\Http;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ReleaseNotification\Model\ContentProvider\Http\UrlBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->urlBuilder = $objectManager->getObject(
            UrlBuilder::class,
            [
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @param string $baseUrl
     * @param string $expected
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl($baseUrl, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->willReturn($baseUrl);
        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->willReturn(1);
        $this->assertEquals(
            $expected,
            $this->urlBuilder->getUrl('version', 'edition', 'locale')
        );
    }

    public function getUrlDataProvider()
    {
        return [
            ['content/url/example/', 'https://content/url/example/version-edition-locale.json#'],
            ['', '']
        ];
    }
}
