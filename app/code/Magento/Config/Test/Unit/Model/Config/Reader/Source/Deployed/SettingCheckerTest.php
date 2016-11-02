<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config;
use Magento\Framework\App\DeploymentConfig;

/**
 * Test class for checking settings that defined in config file
 *
 * @package Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed
 */
class SettingCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var SettingChecker
     */
    private $checker;

    /**
     * @var ScopeCodeResolver | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeCodeResolver;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeCodeResolver = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checker = new SettingChecker($this->config, $this->scopeCodeResolver);
    }

    public function testIsDefined()
    {
        $path = 'general/web/locale';
        $scope = 'website';
        $scopeCode = 'myWebsite';
        $scopeCodeId = '4';

        $this->config->expects($this->once())
            ->method('get')
            ->willReturn([
                $scope => [
                    $scopeCode => [
                        $path => 'value'
                    ],
                ],
            ]);

        $this->scopeCodeResolver->expects($this->once())
            ->method('resolve')
            ->with($scope, $scopeCodeId)
            ->willReturn($scopeCode);

        $this->assertTrue($this->checker->isReadOnly($path, $scope, $scopeCodeId));
    }
}
