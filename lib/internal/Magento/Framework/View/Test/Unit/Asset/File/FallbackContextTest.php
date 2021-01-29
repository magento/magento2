<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\File;

/**
 * @covers \Magento\Framework\View\Asset\File\FallbackContext
 */
class FallbackContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\Asset\File\FallbackContext
     */
    protected $fallbackContext;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            \Magento\Framework\View\Asset\File\FallbackContext::class,
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
    public function getConfigPathDataProvider()
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
