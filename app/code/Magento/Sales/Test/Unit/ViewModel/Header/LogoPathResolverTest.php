<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\ViewModel\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\ViewModel\Header\LogoPathResolver;
use Magento\Store\Model\ScopeInterface;
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
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * Test for case when app in single store mode
     * and logo path is defined in config
     * @return void
     */
    public function testGetPathWhenInSingleStoreModeAndSalesLogoPathNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return "1";
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return 'sales_identity_logo_html_value';
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertEquals('sales/store/logo_html/sales_identity_logo_html_value', $valueForAssert);
        $this->assertNotNull($valueForAssert);
    }

    /**
     * Test for case when app in single store mode
     * and logo path is not defined in config
     * and header logo path is defined in config
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testGetPathWhenInSingleStoreModeAndSalesLogoPathIsNullAndHeaderLogoPathIsNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return '1';
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return null;
                } elseif ($arg1 == 'design/header/logo_src' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
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
     * and header logo path is not defined in config
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testGetPathWhenInSingleStoreModeAndSalesLogoPathIsNullAndHeaderLogoPathIsNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return '1';
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return null;
                } elseif ($arg1 == 'design/header/logo_src' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return null;
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertNull($valueForAssert);
    }

    /**
     * Test for case when app in multi store mode
     * and logo path is defined in config
     * @return void
     */
    public function testGetPathWhenInMultiStoreModeAndPathNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return '0';
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_STORE &&
                    $arg3 == 1) {
                    return 'sales_identity_logo_html_value';
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertEquals('sales/store/logo_html/sales_identity_logo_html_value', $valueForAssert);
        $this->assertNotNull($valueForAssert);
    }

    /**
     * Test for case when app in single store mode
     * and logo path is not defined in config
     * and header logo path is not defined in config
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testGetPathWhenInMultiStoreModeAndSalesLogoPathIsNullAndHeaderLogoPathIsNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return '0';
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_STORE &&
                    $arg3 == 1) {
                    return null;
                } elseif ($arg1 == 'design/header/logo_src' && $arg2 == ScopeInterface::SCOPE_STORE &&
                    $arg3 == 1) {
                    return null;
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertNull($valueForAssert);
    }

    /**
     * Test for case when app in single store mode
     * and logo path is not defined in config
     * and header logo path is defined in config
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testGetPathWhenInMultiStoreModeAndSalesLogoPathIsNullAndHeaderLogoPathIsNotNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'general/single_store_mode/enabled' && $arg2 == ScopeConfigInterface::SCOPE_TYPE_DEFAULT &&
                    $arg3 == null) {
                    return '1';
                } elseif ($arg1 == 'sales/identity/logo_html' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return null;
                } elseif ($arg1 == 'design/header/logo_src' && $arg2 == ScopeInterface::SCOPE_WEBSITE &&
                    $arg3 == 1) {
                    return 'MultiStore.png';
                }
            });
        $valueForAssert = $this->model->getPath();
        $this->assertEquals('logo/MultiStore.png', $valueForAssert);
        $this->assertNotNull($valueForAssert);
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->registry = $this->createMock(Registry::class);
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getStoreId')
            ->willReturn(1);
        $this->registry->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);
        $this->model = new LogoPathResolver($this->scopeConfig, $this->registry);
    }
}
