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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

class CssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Helper\Css
     */
    protected $_object;

    protected function setUp()
    {
        $filesystem = new \Magento\Filesystem(new \Magento\Filesystem\Adapter\Local());
        $dirs = new \Magento\App\Dir('/base_dir');
        $this->_object = new \Magento\Core\Helper\Css($filesystem, $dirs);
    }

    /**
     * @param string $cssContent
     * @param string $originalPath
     * @param string $newPath
     * @param callable $callback
     * @param string $expected
     * @dataProvider replaceCssRelativeUrlsDataProvider
     */
    public function testReplaceCssRelativeUrls($cssContent, $originalPath, $newPath, $callback, $expected)
    {
        $actual = $this->_object->replaceCssRelativeUrls($cssContent, $originalPath, $newPath, $callback);
        $this->assertEquals($expected, $actual);
    }

    public static function replaceCssRelativeUrlsDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        $callback = function ($relativeUrl) {
            return '/base_dir/pub/assets/referenced/dir/' . $relativeUrl;
        };
        $callbackWindows = function ($relativeUrl) {
            return '/base_dir/pub\assets/referenced\dir/' . $relativeUrl;
        };
        $callbackByOrigPath = function ($relativeUrl, $originalPath) {
            return dirname($originalPath) . '/' . $relativeUrl;
        };

        $object = new \Magento\Object(array('resolved_path' => array('body.gif' => '/base_dir/pub/dir/body.gif')));
        $objectCallback = array($object, 'getResolvedPath');

        $source = file_get_contents($fixturePath . 'source.css');
        $result = file_get_contents($fixturePath . 'result.css');

        return array(
            'standard parsing' => array(
                $source,
                '/does/not/matter.css',
                '/base_dir/pub/assets/new/location/any_new_name.css',
                $callback,
                $result,
            ),
            'back slashes in new name' => array(
                $source,
                '/does/not/matter.css',
                '/base_dir\pub/assets\new/location/any_new_name.css',
                $callback,
                $result,
            ),
            'back slashes in referenced name' => array(
                $source,
                '/does/not/matter.css',
                '/base_dir/pub/assets/new/location/any_new_name.css',
                $callbackWindows,
                $result,
            ),
            'same directory' => array(
                $source,
                '/does/not/matter.css',
                '/base_dir/pub/assets/referenced/dir/any_new_name.css',
                $callback,
                $source,
            ),
            'directory with superset name' => array(
                'body {background: url(body.gif);}',
                '/base_dir/pub/assets/referenced/dir/original.css',
                '/base_dir/pub/assets/referenced/dirname/any_new_name.css',
                null,
                'body {background: url(../dir/body.gif);}',
            ),
            'directory with subset name' => array(
                'body {background: url(body.gif);}',
                '/base_dir/pub/assets/referenced/dir/original.css',
                '/base_dir/pub/assets/referenced/di/any_new_name.css',
                null,
                'body {background: url(../dir/body.gif);}',
            ),
            'objectCallback' => array(
                'body {background: url(body.gif);}',
                '/does/not/matter.css',
                '/base_dir/pub/any_new_name.css',
                $objectCallback,
                'body {background: url(dir/body.gif);}',
            ),
            'default resolution without a callback' => array(
                'body {background: url(../body.gif);}',
                '/base_dir/pub/original/subdir/original_name.css',
                '/base_dir/pub/new/subdir/any_new_name.css',
                null,
                'body {background: url(../../original/body.gif);}',
            ),
            'callback must receive original path' => array(
                'body {background: url(../body.gif);}',
                '/base_dir/pub/original/subdir/original_name.css',
                '/base_dir/pub/new/subdir/any_new_name.css',
                $callbackByOrigPath,
                'body {background: url(../../original/body.gif);}',
            ),
        );
    }

    /**
     * @param string $originalFile
     * @param string $newFile
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Offset can be calculated for internal resources only.
     * @dataProvider replaceCssRelativeUrlsExceptionDataProvider
     */
    public function testReplaceCssRelativeUrlsException($originalFile, $newFile)
    {
        $this->_object->replaceCssRelativeUrls('body {background: url(body.gif);}', $originalFile, $newFile);
    }

    /**
     * @return array
     */
    public static function replaceCssRelativeUrlsExceptionDataProvider()
    {
        return array(
            'new css path is out of reach' => array(
                '/base_dir/pub/css/file.css',
                '/not/base_dir/pub/new/file.css',
            ),
            'referenced path is out of reach' => array(
                '/not/base_dir/pub/css/file.css',
                '/base_dir/pub/new/file.css',
            ),
        );
    }
}
