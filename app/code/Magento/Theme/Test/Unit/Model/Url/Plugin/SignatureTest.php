<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Url\Plugin;

use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Url\ConfigInterface;
use Magento\Theme\Model\Url\Plugin\Signature;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    /**
     * @var Signature
     */
    private $object;

    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $deploymentVersion;

    protected function setUp(): void
    {
        $this->config = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->deploymentVersion = $this->createMock(Version::class);
        $this->object = new Signature($this->config, $this->deploymentVersion);
    }

    /**
     * @param bool|int $fixtureConfigFlag
     * @param string $inputUrlType
     * @dataProvider afterGetBaseUrlInactiveDataProvider
     */
    public function testAfterGetBaseUrlInactive($fixtureConfigFlag, $inputUrlType)
    {
        $this->config
            ->expects($this->any())
            ->method('getValue')
            ->with(Signature::XML_PATH_STATIC_FILE_SIGNATURE)
            ->willReturn($fixtureConfigFlag);
        $this->deploymentVersion->expects($this->never())->method($this->anything());

        $url = $this->getMockForAbstractClass(ScopeInterface::class);
        $actualResult = $this->object->afterGetBaseUrl($url, 'http://127.0.0.1/magento/pub/static/', $inputUrlType);
        $this->assertEquals('http://127.0.0.1/magento/pub/static/', $actualResult);
    }

    /**
     * @return array
     */
    public function afterGetBaseUrlInactiveDataProvider()
    {
        return [
            'disabled in config, relevant URL type'  => [0, UrlInterface::URL_TYPE_STATIC],
            'enabled in config, irrelevant URL type' => [1, UrlInterface::URL_TYPE_LINK],
        ];
    }

    public function testAroundGetBaseUrlActive()
    {
        $this->config
            ->expects($this->once())
            ->method('getValue')
            ->with(Signature::XML_PATH_STATIC_FILE_SIGNATURE)
            ->willReturn(1);
        $this->deploymentVersion->expects($this->once())->method('getValue')->willReturn('123');

        $url = $this->getMockForAbstractClass(ScopeInterface::class);
        $actualResult = $this->object->afterGetBaseUrl(
            $url,
            'http://127.0.0.1/magento/pub/static/',
            UrlInterface::URL_TYPE_STATIC
        );
        $this->assertEquals('http://127.0.0.1/magento/pub/static/version123/', $actualResult);
    }
}
