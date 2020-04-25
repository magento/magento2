<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for view filesystem model
 */
namespace Magento\Framework\View\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\FileSystem;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Design\FileResolution\Fallback\File;
use Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile;
use Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile;
use Magento\Framework\View\Design\FileResolution\Fallback\StaticFile;
use Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Setup\Module\I18n\Locale;

class FileSystemTest extends TestCase
{
    /**
     * @var FileSystem|MockObject
     */
    protected $_model;

    /**
     * @var File|MockObject
     */
    protected $_fileResolution;

    /**
     * @var TemplateFile|MockObject
     */
    protected $_templateFileResolution;

    /**
     * @var LocaleFile|MockObject
     */
    protected $_localeFileResolution;

    /**
     * @var StaticFile|MockObject
     */
    protected $_staticFileResolution;

    /**
     * @var EmailTemplateFile
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_emailTemplateFileResolution;

    /**
     * @var Repository|MockObject
     */
    protected $_assetRepo;

    protected function setUp(): void
    {
        $this->_fileResolution = $this->createMock(File::class);
        $this->_templateFileResolution = $this->createMock(
            TemplateFile::class
        );
        $this->_localeFileResolution = $this->createMock(
            LocaleFile::class
        );
        $this->_staticFileResolution = $this->createMock(
            StaticFile::class
        );
        $this->_emailTemplateFileResolution = $this->createMock(
            EmailTemplateFile::class
        );
        $this->_assetRepo = $this->createPartialMock(
            Repository::class,
            ['extractScope', 'updateDesignParams', 'createAsset']
        );

        $this->_model = new FileSystem(
            $this->_fileResolution,
            $this->_templateFileResolution,
            $this->_localeFileResolution,
            $this->_staticFileResolution,
            $this->_emailTemplateFileResolution,
            $this->_assetRepo
        );
    }

    public function testGetFilename()
    {
        $params = [
            'area' => 'some_area',
            'themeModel' => $this->createMock(ThemeInterface::class),
            'module' => 'Some_Module',   //It should be set in \Magento\Framework\View\Asset\Repository::extractScope
                                        // but PHPUnit has troubles with passing arguments by reference
        ];
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_fileResolution->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], 'some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $this->_assetRepo->expects($this->any())
            ->method('extractScope')
            ->with($file, $params)
            ->will($this->returnValue('some_file.ext'));

        $actual = $this->_model->getFilename($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetTemplateFileName()
    {
        $params = [
            'area'       => 'some_area',
            'themeModel' => $this->createMock(ThemeInterface::class),
            'module'     => 'Some_Module', //It should be set in \Magento\Framework\View\Asset\Repository::extractScope
                                           // but PHPUnit has troubles with passing arguments by reference
        ];
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_templateFileResolution->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], 'some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $this->_assetRepo->expects($this->any())
            ->method('extractScope')
            ->with($file, $params)
            ->will($this->returnValue('some_file.ext'));

        $actual = $this->_model->getTemplateFileName($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetLocaleFileName()
    {
        $params = [
            'area' => 'some_area',
            'themeModel' => $this->createMock(ThemeInterface::class),
            'locale' => 'some_locale',
        ];
        $file = 'some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_localeFileResolution->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], $params['locale'], 'some_file.ext')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getLocaleFileName($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetViewFile()
    {
        $params = [
            'area' => 'some_area',
            'themeModel' => $this->createMock(ThemeInterface::class),
            'locale' => 'some_locale',
            'module' => 'Some_Module',
        ];
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_staticFileResolution->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], $params['locale'], 'some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getStaticFileName($file, $params);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $path
     * @param string $expectedResult
     * @dataProvider normalizePathDataProvider
     */
    public function testNormalizePath($path, $expectedResult)
    {
        $result = $this->_model->normalizePath($path);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function normalizePathDataProvider()
    {
        return [
            'standard path' => ['/dir/somedir/somefile.ext', '/dir/somedir/somefile.ext'],
            'one dot path' => ['/dir/somedir/./somefile.ext', '/dir/somedir/somefile.ext'],
            'two dots path' => ['/dir/somedir/../somefile.ext', '/dir/somefile.ext'],
            'two times two dots path' => ['/dir/../somedir/../somefile.ext', '/somefile.ext']
        ];
    }

    /**
     * @param string $relatedPath
     * @param string $path
     * @param string $expectedResult
     * @dataProvider offsetPathDataProvider
     */
    public function testOffsetPath($relatedPath, $path, $expectedResult)
    {
        $result = $this->_model->offsetPath($relatedPath, $path);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function offsetPathDataProvider()
    {
        return [
            'local path' => [
                '/some/directory/two/another/file.ext',
                '/some/directory/one/file.ext',
                '../two/another',
            ],
            'local path reverted' => [
                '/some/directory/one/file.ext',
                '/some/directory/two/another/file.ext',
                '../../one',
            ],
            'url' => [
                'http://example.com/images/logo.gif',
                'http://example.com/themes/demo/css/styles.css',
                '../../../images',
            ],
            'same path' => [
                '/some/directory/file.ext',
                '/some/directory/file1.ext',
                '.',
            ],
            'non-normalized' => [
                '/some/directory/../one/file.ext',
                '/some/directory/./two/another/file.ext',
                '../../../one',
            ],
        ];
    }

    public function testGetEmailTemplateFile()
    {
        $locale = Locale::DEFAULT_SYSTEM_LOCALE;
        $params = [
            'area'       => 'some_area',
            'themeModel' => $this->createMock(ThemeInterface::class),
            'module'     => 'Some_Module',
            'locale'     => $locale
        ];
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $this->_emailTemplateFileResolution->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], $locale, $file, 'Some_Module')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getEmailTemplateFileName($file, $params, 'Some_Module');
        $this->assertEquals($expected, $actual);
    }
}
