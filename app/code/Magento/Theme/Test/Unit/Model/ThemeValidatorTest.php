<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Config\Value;
use Magento\Framework\DataObject;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ThemeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemeValidatorTest extends TestCase
{
    /**
     * @var ThemeValidator
     */
    protected $themeValidator;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var ThemeProviderInterface|MockObject
     */
    protected $themeProvider;

    /**
     * @var Value|MockObject
     */
    protected $configData;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->themeProvider = $this->getMockForAbstractClass(ThemeProviderInterface::class);
        $this->configData = $this->getMockBuilder(Value::class)
            ->addMethods(['addFieldToFilter'])
            ->onlyMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeValidator = new ThemeValidator(
            $this->storeManager,
            $this->themeProvider,
            $this->configData
        );
    }

    public function testValidateIsThemeInUse()
    {
        $theme = $this->createMock(Theme::class);
        $theme->expects($this->once())->method('getId')->willReturn(6);
        $defaultEntity = new DataObject(['value' => 6, 'scope' => 'default', 'scope_id' => 8]);
        $websitesEntity = new DataObject(['value' => 6, 'scope' => 'websites', 'scope_id' => 8]);
        $storesEntity = new DataObject(['value' => 6, 'scope' => 'stores', 'scope_id' => 8]);
        $this->themeProvider->expects($this->once())->method('getThemeByFullPath')->willReturn($theme);
        $this->configData->expects($this->once())->method('getCollection')->willReturn($this->configData);
        $this->configData
            ->expects($this->at(1))
            ->method('addFieldToFilter')
            ->willReturn($this->configData);
        $this->configData
            ->expects($this->at(2))
            ->method('addFieldToFilter')
            ->willReturn([$defaultEntity, $websitesEntity, $storesEntity]);
        $website = $this->createPartialMock(Website::class, ['getName']);
        $website->expects($this->once())->method('getName')->willReturn('websiteA');
        $store = $this->createPartialMock(Store::class, ['getName']);
        $store->expects($this->once())->method('getName')->willReturn('storeA');
        $this->storeManager->expects($this->once())->method('getWebsite')->willReturn($website);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $result = $this->themeValidator->validateIsThemeInUse(['frontend/Magento/a']);
        $this->assertEquals(
            [
                '<error>frontend/Magento/a is in use in default config</error>',
                '<error>frontend/Magento/a is in use in website websiteA</error>',
                '<error>frontend/Magento/a is in use in store storeA</error>'
            ],
            $result
        );
    }
}
