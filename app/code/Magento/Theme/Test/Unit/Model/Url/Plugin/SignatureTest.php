<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Theme\Test\Unit\Model\Url\Plugin;

use \Magento\Theme\Model\Url\Plugin\Signature;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Signature
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentVersion;

    /**
     * @var callable
     */
    private $closureMock;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Framework\View\Url\ConfigInterface');
        $this->deploymentVersion = $this->getMock(
            'Magento\Framework\App\View\Deployment\Version', [], [], '', false
        );
        $this->closureMock = function () {
            return 'http://127.0.0.1/magento/pub/static/';
        };
        $this->object = new Signature($this->config, $this->deploymentVersion);
    }

    /**
     * @param bool|int $fixtureConfigFlag
     * @param string $inputUrlType
     * @dataProvider aroundGetBaseUrlInactiveDataProvider
     */
    public function testAroundGetBaseUrlInactive($fixtureConfigFlag, $inputUrlType)
    {
        $this->config
            ->expects($this->any())
            ->method('getValue')
            ->with(Signature::XML_PATH_STATIC_FILE_SIGNATURE)
            ->will($this->returnValue($fixtureConfigFlag));
        $this->deploymentVersion->expects($this->never())->method($this->anything());

        $url = $this->getMockForAbstractClass('\Magento\Framework\Url\ScopeInterface');
        $actualResult = $this->object->aroundGetBaseUrl($url, $this->closureMock, $inputUrlType);
        $this->assertEquals('http://127.0.0.1/magento/pub/static/', $actualResult);
    }

    /**
     * @return array
     */
    public function aroundGetBaseUrlInactiveDataProvider()
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
            ->will($this->returnValue(1));
        $this->deploymentVersion->expects($this->once())->method('getValue')->will($this->returnValue('123'));

        $url = $this->getMockForAbstractClass('\Magento\Framework\Url\ScopeInterface');
        $actualResult = $this->object->aroundGetBaseUrl(
            $url, $this->closureMock, \Magento\Framework\UrlInterface::URL_TYPE_STATIC
        );
        $this->assertEquals('http://127.0.0.1/magento/pub/static/version123/', $actualResult);
    }
}
