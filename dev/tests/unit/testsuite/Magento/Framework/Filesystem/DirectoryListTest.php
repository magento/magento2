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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filesystem;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem as AppFilesystem;

class DirectoryListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for add directory and getConfig methods
     *
     * @dataProvider addDirectoryGetConfigDataProvider
     * @param string $root
     * @param array $directories
     * @param array $configs
     * @param array $expectedConfig
     */
    public function testAddDirectoryGetConfig($root, array $directories, array $configs, array $expectedConfig)
    {
        $directoryList = new DirectoryList($root, $directories);
        foreach ($configs as $code => $config) {
            $directoryList->addDirectory($code, $config);
            $this->assertEquals($expectedConfig[$code], $directoryList->getConfig($code));
        }
    }

    public function addDirectoryGetConfigDataProvider()
    {
        return array(
            'static_view' => array(
                __DIR__,
                array(),
                array(
                    'custom2_' . AppFilesystem::STATIC_VIEW_DIR => array(
                        'path' => 'some/static',
                        'uri' => 'some/static',
                        'permissions' => 0777,
                        'read_only' => true,
                        'allow_create_dirs' => true
                    )
                ),
                array(
                    'custom2_' . AppFilesystem::STATIC_VIEW_DIR => array(
                        'path' => str_replace('\\', '/', __DIR__ . '/some/static'),
                        'uri' => 'some/static',
                        'permissions' => 0777,
                        'read_only' => true,
                        'allow_create_dirs' => true
                    )
                ),
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testAddDefinedDirectory()
    {
        $directories = array(AppFilesystem::STATIC_VIEW_DIR => array('path' => ''));
        $directoryList = new DirectoryList(__DIR__, $directories);
        $directoryList->addDirectory(AppFilesystem::STATIC_VIEW_DIR, array('path' => ''));
    }

    /**
     * Test for creating DirectoryList with invalid URI
     *
     * @param string $code
     * @param string $value
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidUriDataProvider
     */
    public function testInvalidUri($code, $value)
    {
        new DirectoryList(__DIR__, array($code => array('uri' => $value)));
    }

    /**
     * Data provider for testInvalidUri
     *
     * @return array
     */
    public function invalidUriDataProvider()
    {
        return array(
            array(AppFilesystem::MEDIA_DIR, '/'),
            array(AppFilesystem::MEDIA_DIR, '//'),
            array(AppFilesystem::MEDIA_DIR, '/value'),
            array(AppFilesystem::MEDIA_DIR, 'value/'),
            array(AppFilesystem::MEDIA_DIR, '/value/'),
            array(AppFilesystem::MEDIA_DIR, 'one\\two'),
            array(AppFilesystem::MEDIA_DIR, '../dir'),
            array(AppFilesystem::MEDIA_DIR, './dir'),
            array(AppFilesystem::MEDIA_DIR, 'one/../two')
        );
    }

    /**
     * Test for getting uri from DirectoryList
     */
    public function testGetUri()
    {
        $dir = new DirectoryList(
            __DIR__,
            array(
                AppFilesystem::PUB_DIR => array('uri' => ''),
                AppFilesystem::MEDIA_DIR => array('uri' => 'test'),
                'custom' => array('uri' => 'test2')
            )
        );

        $this->assertEquals('test2', $dir->getConfig('custom')['uri']);
        $this->assertEquals('', $dir->getConfig(AppFilesystem::PUB_DIR)['uri']);
        $this->assertEquals('test', $dir->getConfig(AppFilesystem::MEDIA_DIR)['uri']);
    }

    /**
     * Test for getting directory path from DirectoryList
     */
    public function testGetDir()
    {
        $newRoot = __DIR__ . '/root';
        $newMedia = __DIR__ . '/media';
        $dir = new DirectoryList(
            __DIR__,
            array(
                AppFilesystem::ROOT_DIR => array('path' => $newRoot),
                AppFilesystem::MEDIA_DIR => array('path' => $newMedia),
                'custom' => array('path' => 'test2')
            )
        );

        $this->assertEquals('test2', $dir->getDir('custom'));
        $this->assertEquals(str_replace('\\', '/', $newRoot), $dir->getConfig(AppFilesystem::ROOT_DIR)['path']);
        $this->assertEquals(str_replace('\\', '/', $newMedia), $dir->getConfig(AppFilesystem::MEDIA_DIR)['path']);
    }

    public function testIsConfigured()
    {
        $dir = new DirectoryList(__DIR__, array(AppFilesystem::PUB_DIR => array('uri' => '')));

        $this->assertTrue($dir->isConfigured(AppFilesystem::PUB_DIR));
        $this->assertFalse($dir->isConfigured(AppFilesystem::MEDIA_DIR));
    }
}
