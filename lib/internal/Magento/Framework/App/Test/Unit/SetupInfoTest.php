<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\SetupInfo;

class SetupInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A default fixture
     *
     * @var array
     */
    private static $fixture = ['DOCUMENT_ROOT' => '/doc/root', 'SCRIPT_FILENAME' => '/doc/root/dir/file.php'];

    /**
     * @param array $server
     * @param string $expectedError
     * @dataProvider constructorExceptionsDataProvider
     */
    public function testConstructorExceptions($server, $expectedError)
    {
        $this->setExpectedException('\InvalidArgumentException', $expectedError);
        new SetupInfo($server);
    }

    public function constructorExceptionsDataProvider()
    {
        $docRootErr = 'DOCUMENT_ROOT variable is unavailable.';
        $projectRootErr = 'Project root cannot be automatically detected.';
        return [
            [[], $docRootErr],
            [['DOCUMENT_ROOT' => ''], $docRootErr],
            [['DOCUMENT_ROOT' => '/foo'], $projectRootErr],
            [['DOCUMENT_ROOT' => '/foo', 'SCRIPT_FILENAME' => ''], $projectRootErr],
        ];
    }

    /**
     * @param array $server
     * @param string $expected
     * @dataProvider getUrlDataProvider
     */
    public function testGetUrl($server, $expected)
    {
        $info = new SetupInfo($server);
        $this->assertEquals($expected, $info->getUrl());
    }

    /**
     * @return array
     */
    public function getUrlDataProvider()
    {
        return [
            [
                self::$fixture,
                '/setup/'
            ],
            [
                self::$fixture + [SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => 'install'],
                '/install/',
            ],
            [
                self::$fixture + [SetupInfo::PARAM_NOT_INSTALLED_URL => 'http://example.com/'],
                'http://example.com/',
            ],
        ];
    }

    /**
     * @param array $server
     * @param string $expected
     * @dataProvider getProjectUrlDataProvider
     */
    public function testGetProjectUrl($server, $expected)
    {
        $info = new SetupInfo($server);
        $this->assertEquals($expected, $info->getProjectUrl());
    }

    /**
     * @return array
     */
    public function getProjectUrlDataProvider()
    {
        return [
            [self::$fixture, ''],
            [self::$fixture + ['HTTP_HOST' => ''], ''],
            [
                ['DOCUMENT_ROOT' => '/foo/bar', 'SCRIPT_FILENAME' => '/other/baz.php', 'HTTP_HOST' => 'example.com'],
                'http://example.com/'
            ],
            [self::$fixture + ['HTTP_HOST' => 'example.com'], 'http://example.com/dir/'],
            [
                ['DOCUMENT_ROOT' => '/foo/bar', 'SCRIPT_FILENAME' => '/foo/bar/baz.php', 'HTTP_HOST' => 'example.com'],
                'http://example.com/'
            ],
        ];
    }

    /**
     * @param array $server
     * @param string $projectRoot
     * @param string $expected
     * @dataProvider getDirDataProvider
     */
    public function testGetDir($server, $projectRoot, $expected)
    {
        $info = new SetupInfo($server);
        $this->assertEquals($expected, $info->getDir($projectRoot));
    }

    /**
     * @return array
     */
    public function getDirDataProvider()
    {
        return [
            [
                self::$fixture,
                '/test/root',
                '/test/root/setup',
            ],
            [
                self::$fixture,
                '/test/root/',
                '/test/root/setup',
            ],
            [
                self::$fixture + [SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => '/install/'],
                '/test/',
                '/test/install',
            ],
        ];
    }

    /**
     * @param array $server
     * @param bool $expected
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($server, $expected)
    {
        $info = new SetupInfo($server);
        $this->assertEquals($expected, $info->isAvailable());
    }

    /**
     * @return array
     */
    public function isAvailableDataProvider()
    {
        $server = ['DOCUMENT_ROOT' => __DIR__, 'SCRIPT_FILENAME' => __FILE__];
        return [
            'root = doc root, but no "setup" sub-directory' => [
                $server, // it will look for "setup/" sub-directory, but won't find anything
                false
            ],
            'root = doc root, nonexistent sub-directory' => [
                $server + [SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => 'nonexistent'],
                false
            ],
            'root = doc root, existent sub-directory' => [
                $server + [SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => '_files'],
                true
            ],
            'root within doc root, existent sub-directory' => [
                [
                    'DOCUMENT_ROOT' => dirname(__DIR__),
                    'SCRIPT_FILENAME' => __FILE__,
                    SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => '_files'
                ],
                true
            ],
            'root outside of doc root, existent sub-directory' => [
                [
                    'DOCUMENT_ROOT' => __DIR__,
                    'SCRIPT_FILENAME' => dirname(dirname(__DIR__)) . '/foo.php',
                    SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => basename(__DIR__)
                ],
                false
            ],
            'root within doc root, existent sub-directory, trailing slash' => [
                [
                    'DOCUMENT_ROOT' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
                    'SCRIPT_FILENAME' => __FILE__,
                    SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => '_files'
                ],
                true
            ],
        ];
    }
}
