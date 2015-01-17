<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\CacheData;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $theme;

    /**
     * @var Flat
     */
    private $object;

    protected function setUp()
    {
        $this->cache = $this->getMock(
            '\Magento\Framework\View\Design\FileResolution\Fallback\Cache', [], [], '', false
        );

        $this->theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');

        $this->object = new \Magento\Framework\View\Design\FileResolution\Fallback\CacheData\Flat($this->cache);
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string $expectedId
     * @param string $expectedValue
     *
     * @dataProvider cacheDataProvider
     */
    public function testGetFromCache($area, $themePath, $locale, $module, $expectedId, $expectedValue)
    {
        if (isset($params['theme'])) {
            $this->theme->expects($this->any())
                ->method('getThemePath')
                ->will($this->returnValue($params['theme']));
            $params['theme'] = $this->theme;
        } else {
            $this->theme->expects($this->never())
                ->method('getThemePath');
        }

        $this->cache->expects($this->once())
            ->method('load')
            ->with($expectedId)
            ->will($this->returnValue($expectedValue));

        $actual = $this->object->getFromCache('file', 'file.ext', $area, $themePath, $locale, $module);
        $this->assertSame($expectedValue, $actual);
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string $expectedId
     * @param string $savedValue
     *
     * @dataProvider cacheDataProvider
     */
    public function testSaveToCache($area, $themePath, $locale, $module, $expectedId, $savedValue)
    {
        if (isset($params['theme'])) {
            $this->theme->expects($this->any())
                ->method('getThemePath')
                ->will($this->returnValue($params['theme']));
            $params['theme'] = $this->theme;
        } else {
            $this->theme->expects($this->never())
                ->method('getThemePath');
        }

        $this->cache->expects($this->once())
            ->method('save')
            ->with($savedValue, $expectedId)
            ->will($this->returnValue(true));

        $actual = $this->object->saveToCache($savedValue, 'file', 'file.ext', $area, $themePath, $locale, $module);
        $this->assertTrue($actual);
    }

    /**
     * @return array
     */
    public function cacheDataProvider()
    {
        return [
            'all params' => [
                'frontend', 'magento_theme', 'en_US', 'Magento_Module',
                'type:file|area:frontend|theme:magento_theme|locale:en_US|module:Magento_Module|file:file.ext',
                'one/file.ext',
            ],
            'no area' => [
                null, 'magento_theme', 'en_US', 'Magento_Module',
                'type:file|area:|theme:magento_theme|locale:en_US|module:Magento_Module|file:file.ext',
                'two/file.ext',
            ],
            'no theme' => [
                'frontend', null, 'en_US', 'Magento_Module',
                'type:file|area:frontend|theme:|locale:en_US|module:Magento_Module|file:file.ext',
                'three/file.ext',
            ],
            'no locale' => [
                'frontend', 'magento_theme', null, 'Magento_Module',
                'type:file|area:frontend|theme:magento_theme|locale:|module:Magento_Module|file:file.ext',
                'four/file.ext',
            ],
            'no module' => [
                'frontend', 'magento_theme', 'en_US', null,
                'type:file|area:frontend|theme:magento_theme|locale:en_US|module:|file:file.ext',
                'five/file.ext',
            ],
        ];
    }
}
