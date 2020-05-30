<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider;
use Magento\Framework\App\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Weee\Model\Config as WeeeConfig;
use Magento\Weee\Plugin\Ui\DataProvider\WeeeSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WeeeSettingsTest extends TestCase
{
    /**
     * Stub settings fpt display product list
     */
    private const STUB_FPT_DISPLAY_PRODUCT_LIST = '1';

    /**
     * @var WeeeSettings
     */
    private $plugin;

    /**
     * @var DataProvider|MockObject
     */
    protected $subjectMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * Prepare environment for test
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->subjectMock = $this->createMock(DataProvider::class);

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            WeeeSettings::class,
            [
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test plugin afterGetData
     */
    public function testAfterGetDataWhenConfigIsYesResultIsEmpty()
    {
        $this->configMock->expects($this->any())->method('getValue')
            ->with(WeeeConfig::XML_PATH_FPT_DISPLAY_PRODUCT_LIST)
            ->willReturn(self::STUB_FPT_DISPLAY_PRODUCT_LIST);

        $this->assertEquals(
            [
                'displayWeee' => self::STUB_FPT_DISPLAY_PRODUCT_LIST
            ],
            $this->plugin->afterGetData($this->subjectMock, [])
        );
    }
}
