<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\File;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File\FallbackContext;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Framework\View\Asset\File\FallbackContext
 */
class FallbackContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var FallbackContext
     */
    protected $fallbackContext;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @covers \Magento\Framework\View\Asset\File\FallbackContext::getConfigPath
     * @param string $baseUrl
     * @param string $areaType
     * @param string $themePath
     * @param string $localeCode
     * @param string $expectedResult
     * @dataProvider getConfigPathDataProvider
     */
    public function testGetConfigPath(
        $baseUrl,
        $areaType,
        $themePath,
        $localeCode,
        $expectedResult
    ) {
        $this->fallbackContext = $this->objectManager->getObject(
            FallbackContext::class,
            [
                'baseUrl' => $baseUrl,
                'areaType' => $areaType,
                'themePath' => $themePath,
                'localeCode' => $localeCode
            ]
        );
        $this->assertEquals($expectedResult, $this->fallbackContext->getConfigPath());
    }

    /**
     * @return array
     */
    public static function getConfigPathDataProvider()
    {
        return [
            'http' => [
                'baseUrl' => 'http://some-name.com/pub/static/',
                'areaType' => 'frontend',
                'themePath' => 'Magento/blank',
                'localeCode' => 'en_US',
                'expectedResult' => 'frontend/Magento/blank/en_US'
            ],
            'https' => [
                'baseUrl' => 'https://some-name.com/pub/static/',
                'areaType' => 'frontend',
                'themePath' => 'Magento/blank',
                'localeCode' => 'en_US',
                'expectedResult' => 'frontend/Magento/blank/en_US'
            ]
        ];
    }
}
