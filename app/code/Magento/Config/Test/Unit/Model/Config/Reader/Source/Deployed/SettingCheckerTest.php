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

/**
 * Test class for checking settings that defined in config file
 *
 * @package Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed
 */
class SettingCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeCodeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeCodeResolver;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var SettingChecker
     */
    private $checker;

    public function setUp()
    {
        $this->scopeCodeResolver = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checker = new SettingChecker($this->scopeCodeResolver, $this->config);
    }

    public function testIsDefined()
    {
        $path = 'general/web/locale';
        $scope = 'website';
        $scopeCode = 'myWebsite';

        $this->scopeCodeResolver->expects($this->once())
            ->method('resolve')
            ->with($scope, $scopeCode)
            ->willReturn($scopeCode);
        $this->config->expects($this->once())
            ->method('get')
            ->willReturn([
                $scope => [
                    $scopeCode => [
                        $path => 'value'
                    ],
                ],

            ]);

        $this->assertTrue($this->checker->isReadOnly($path, $scope, $scopeCode));
    }
}
