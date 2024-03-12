<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\ViewModel\Block\Html\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test logo path resolver view model
 */
class LogoPathResolverTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var LogoPathResolver
     */
    private $model;

    /**
     * Test for case when app in single store mode
     * and logo path is defined in config
     * @return void
     */
    public function testGetPathWhenInSingleStoreModeAndPathNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeCode) {
                if ($path === 'general/single_store_mode/enabled' &&
                    $scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $scopeCode === null) {
                    return '1';
                }
                if ($path === 'design/header/logo_src' &&
                    $scope === ScopeInterface::SCOPE_WEBSITE &&
                    $scopeCode === null) {
                    return 'SingleStore.png';
                }
            });

        $valueForAssert = $this->model->getPath();
        $this->assertEquals('logo/SingleStore.png', $valueForAssert);
        $this->assertNotNull($valueForAssert);
    }

    /**
     * Test for case when app in single store mode
     * and logo path is not defined in config
     * @return void
     */
    public function testGetPathWhenInSingleStoreModeAndPathIsNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeCode) {
                if ($path === 'general/single_store_mode/enabled' &&
                    $scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $scopeCode === null) {
                    return '1';
                }
                if ($path === 'design/header/logo_src' &&
                    $scope === ScopeInterface::SCOPE_STORE &&
                    $scopeCode === null) {
                    return null;
                }
            });

        $this->assertNull($this->model->getPath());
    }

    /**
     * Test for case when app in multi store mode
     * and logo path is defined in config
     * @return void
     */
    public function testGetPathWhenInMultiStoreModeAndPathNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeCode) {
                if ($path === 'general/single_store_mode/enabled' &&
                    $scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $scopeCode === null) {
                    return '0';
                }
                if ($path === 'design/header/logo_src' &&
                    $scope === ScopeInterface::SCOPE_STORE &&
                    $scopeCode === null) {
                    return 'MultiStore.png';
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertEquals('logo/MultiStore.png', $valueForAssert);
        $this->assertNotNull($valueForAssert);
    }

    /**
     * Test for case when app in multi store mode
     * and logo path is not defined in config
     * @return void
     */
    public function testGetPathWhenInMultiStoreModeAndPathIsNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeCode) {
                if ($path === 'general/single_store_mode/enabled' &&
                    $scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $scopeCode === null) {
                    return '0';
                }
                if ($path === 'design/header/logo_src' &&
                    $scope === ScopeInterface::SCOPE_STORE &&
                    $scopeCode === null) {
                    return null;
                }
            });
        $this->assertNull($this->model->getPath());
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->model = new LogoPathResolver($this->scopeConfig);
    }
}
