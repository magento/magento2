<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme model
 */
namespace Magento\Theme\Test\Unit\Model;

class ThemeValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\ThemeValidator
     */
    protected $themeValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeProvider;

    /**
     * @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configData;

    protected function setUp()
    {
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->themeProvider = $this->createMock(\Magento\Framework\View\Design\Theme\ThemeProviderInterface::class);
        $this->configData = $this->createPartialMock(
            \Magento\Framework\App\Config\Value::class,
            ['getCollection', 'addFieldToFilter']
        );
        $this->themeValidator = new \Magento\Theme\Model\ThemeValidator(
            $this->storeManager,
            $this->themeProvider,
            $this->configData
        );
    }

    public function testValidateIsThemeInUse()
    {
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $theme->expects($this->once())->method('getId')->willReturn(6);
        $defaultEntity = new \Magento\Framework\DataObject(['value' => 6, 'scope' => 'default', 'scope_id' => 8]);
        $websitesEntity = new \Magento\Framework\DataObject(['value' => 6, 'scope' => 'websites', 'scope_id' => 8]);
        $storesEntity = new \Magento\Framework\DataObject(['value' => 6, 'scope' => 'stores', 'scope_id' => 8]);
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
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getName']);
        $website->expects($this->once())->method('getName')->willReturn('websiteA');
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getName']);
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
