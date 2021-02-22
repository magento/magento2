<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Url\Plugin;

use \Magento\Theme\Model\Url\Plugin\Signature;

class SignatureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Signature
     */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentVersion;

    protected function setUp(): void
    {
        $this->config = $this->createMock(\Magento\Framework\View\Url\ConfigInterface::class);
        $this->deploymentVersion = $this->createMock(\Magento\Framework\App\View\Deployment\Version::class);
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

        $url = $this->getMockForAbstractClass(\Magento\Framework\Url\ScopeInterface::class);
        $actualResult = $this->object->afterGetBaseUrl($url, 'http://127.0.0.1/magento/pub/static/', $inputUrlType);
        $this->assertEquals('http://127.0.0.1/magento/pub/static/', $actualResult);
    }

    /**
     * @return array
     */
    public function afterGetBaseUrlInactiveDataProvider()
    {
        return [
            'disabled in config, relevant URL type'  => [0, \Magento\Framework\UrlInterface::URL_TYPE_STATIC],
            'enabled in config, irrelevant URL type' => [1, \Magento\Framework\UrlInterface::URL_TYPE_LINK],
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

        $url = $this->getMockForAbstractClass(\Magento\Framework\Url\ScopeInterface::class);
        $actualResult = $this->object->afterGetBaseUrl(
            $url,
            'http://127.0.0.1/magento/pub/static/',
            \Magento\Framework\UrlInterface::URL_TYPE_STATIC
        );
        $this->assertEquals('http://127.0.0.1/magento/pub/static/version123/', $actualResult);
    }
}
