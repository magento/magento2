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
namespace Magento\Framework\Filesystem\Driver;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /** @var array Result of get_headers() function */
    public static $headers;

    /** @var string Result of file_get_contents() function */
    public static $fileGetContents;

    /** @var bool Result of file_put_contents() function */
    public static $filePutContents;

    /** @var bool Result of fsockopen() function */
    public static $fsockopen;

    public function setUp()
    {
        require_once __DIR__ . '/../_files/http_mock.php';

        self::$headers = array();
        self::$fileGetContents = '';
        self::$filePutContents = true;
        self::$fsockopen = true;
    }

    /**
     * @dataProvider dataProviderForTestIsExists
     */
    public function testIsExists($status, $result)
    {
        self::$headers = array($status);
        $this->assertEquals($result, (new Http())->isExists(''));
    }

    public function dataProviderForTestIsExists()
    {
        return array(array('200 OK', true), array('404 Not Found', false));
    }

    /**
     * @dataProvider dataProviderForTestStat
     */
    public function testStat($headers, $result)
    {
        self::$headers = $headers;
        $this->assertEquals($result, (new Http())->stat(''));
    }

    public function dataProviderForTestStat()
    {
        $headers1 = array(
            'Content-Length' => 128,
            'Content-Type' => 'type',
            'Last-Modified' => '2013-12-19T17:41:45+00:00',
            'Content-Disposition' => 1024
        );

        $result1 = $this->_resultForStat(
            array('size' => 128, 'type' => 'type', 'mtime' => '2013-12-19T17:41:45+00:00', 'disposition' => 1024)
        );

        return array(array(array(), $this->_resultForStat()), array($headers1, $result1));
    }

    /**
     * Form a result array similar to what stat() produces
     *
     * @param array $nonEmptyValues
     * @return array
     */
    protected function _resultForStat($nonEmptyValues = array())
    {
        $result = array(
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'atime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
            'size' => 0,
            'type' => '',
            'mtime' => 0,
            'disposition' => null
        );

        return array_merge($result, $nonEmptyValues);
    }

    public function testFileGetContents()
    {
        $content = 'some content';
        self::$fileGetContents = $content;
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    public function testFileGetContentsNoContent()
    {
        $content = '';
        self::$fileGetContents = '';
        $this->assertEquals($content, (new Http())->fileGetContents(''));
    }

    public function testFilePutContents()
    {
        self::$filePutContents = true;
        $this->assertTrue((new Http())->filePutContents('', ''));
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testFilePutContentsFail()
    {
        self::$filePutContents = false;
        (new Http())->filePutContents('', '');
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     * @expectedExceptionMessage Please correct the download URL.
     */
    public function testFileOpenInvalidUrl()
    {
        (new Http())->fileOpen('', '');
    }

    public function testFileOpen()
    {
        $fsockopenResult = 'resource';
        self::$fsockopen = $fsockopenResult;
        $this->assertEquals($fsockopenResult, (new Http())->fileOpen('example.com', 'r'));
    }
}

/**
 * Override standard function
 *
 * @return array
 */
function get_headers()
{
    return HttpTest::$headers;
}
