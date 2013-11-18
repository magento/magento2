<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Design\FileResolution\Strategy\Fallback;

use Magento\Filesystem;
use Magento\Filesystem\Adapter\Local;
use Magento\Io\File;
use Magento\TestFramework\Helper\ProxyTesting;

/**
 * CachingProxy Test
 *
 * @package Magento\View
 */
class CachingProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Temp directory for the model to store maps
     *
     * @var string
     */
    protected $tmpDir;

    /**
     * Mock of the model to be tested. Operates the mocked fallback object.
     *
     * @var CachingProxy
     */
    protected $model;

    /**
     * Mocked fallback object, with file resolution methods ready to be substituted.
     *
     * @var \Magento\View\Design\FileResolution\Strategy\Fallback|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fallback;

    /**
     * Theme model, pre-created in setUp() for usage in tests
     *
     * @var \Magento\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeModel;

    protected function setUp()
    {
        $this->tmpDir = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'fallback';
        mkdir($this->tmpDir);

        $this->fallback = $this->getMock(
            'Magento\View\Design\FileResolution\Strategy\Fallback',
            array(),
            array(),
            '',
            false
        );

        $this->themeModel = \PHPUnit_Framework_MockObject_Generator::getMock(
            'Magento\Core\Model\Theme',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->themeModel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('t'));

        $this->model = new CachingProxy(
            $this->fallback,
            $this->createFilesystem(),
            $this->tmpDir,
            TESTS_TEMP_DIR,
            true
        );
    }

    protected function tearDown()
    {
        File::rmdirRecursive($this->tmpDir);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructInvalidDir()
    {
        $this->model = new CachingProxy(
            $this->fallback,
            $this->createFilesystem(),
            $this->tmpDir,
            TESTS_TEMP_DIR . '/invalid_dir'
        );
    }

    public function testDestruct()
    {
        $this->fallback->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'test.txt'));

        $expectedFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'a_t_.ser';

        $this->model->getFile('a', $this->themeModel, 'does not matter', 'Some_Module');
        $this->assertFileNotExists($expectedFile);
        unset($this->model);
        $this->assertFileExists($expectedFile);
        $contents = file_get_contents($expectedFile);
        $this->assertContains('test.txt', $contents);
        $this->assertContains('Some_Module', $contents);
    }

    public function testDestructNoMapSaved()
    {
        $this->fallback->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'test.txt'));
        $model = new CachingProxy(
            $this->fallback,
            $this->createFilesystem(),
            $this->tmpDir,
            TESTS_TEMP_DIR,
            false
        );

        $unexpectedFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'a_t_.ser';

        $model->getFile('a', $this->themeModel, 'does not matter', 'Some_Module');
        unset($model);
        $this->assertFileNotExists($unexpectedFile);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $expectedResult
     * @dataProvider proxyMethodsDataProvider
     * @covers \Magento\View\Design\FileResolution\Strategy\Fallback\CachingProxy::getFile
     * @covers \Magento\View\Design\FileResolution\Strategy\Fallback\CachingProxy::getLocaleFile
     * @covers \Magento\View\Design\FileResolution\Strategy\Fallback\CachingProxy::getViewFile
     */
    public function testProxyMethods($method, $params, $expectedResult)
    {
        $helper = new ProxyTesting();
        $actualResult = $helper->invokeWithExpectations(
            $this->model,
            $this->fallback,
            $method,
            $params,
            $expectedResult
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function proxyMethodsDataProvider()
    {
        $themeModel = \PHPUnit_Framework_MockObject_Generator::getMock(
            'Magento\Core\Model\Theme',
            array(),
            array(),
            '',
            false,
            false
        );

        return array(
            'getFile' => array(
                'getFile',
                array('area51', $themeModel, 'file.txt', 'Some_Module'),
                TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'fallback' . DIRECTORY_SEPARATOR . 'file.txt',
            ),
            'getLocaleFile' => array(
                'getLocaleFile',
                array('area51', $themeModel, 'sq_AL', 'file.txt'),
                'path/to/locale_file.txt',
            ),
            'getViewFile' => array(
                'getViewFile',
                array('area51', $themeModel, 'uk_UA', 'file.txt', 'Some_Module'),
                'path/to/view_file.txt',
            ),
        );
    }

    public function testSetViewFilePathToMap()
    {
        $materializedFilePath = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'file.txt';

        $result = $this->model->setViewFilePathToMap(
            'area51',
            $this->themeModel,
            'en_US',
            'Some_Module',
            'file.txt',
            $materializedFilePath
        );
        $this->assertEquals($this->model, $result);

        $this->fallback->expects($this->never())
            ->method('getViewFile');
        $result = $this->model->getViewFile('area51', $this->themeModel, 'en_US', 'file.txt', 'Some_Module');
        $this->assertEquals($materializedFilePath, $result);
    }

    /**
     * @return Filesystem
     */
    protected function createFilesystem()
    {
        return new Filesystem(new Local());
    }
}
