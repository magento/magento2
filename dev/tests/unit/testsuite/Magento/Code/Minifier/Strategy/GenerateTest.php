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
namespace Magento\Code\Minifier\Strategy;

use Magento\App\Filesystem;
use Magento\Filesystem\Directory\Write;
use Magento\Filesystem\Directory\Read;

class GenerateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var Read | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var Write | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pubViewCacheDir;

    /**
     * @var \Magento\Code\Minifier\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * Set up before each test
     */
    public function setUp()
    {
        $this->rootDirectory = $this->getMock('Magento\Filesystem\Directory\Read', array(), array(), '', false);
        $this->pubViewCacheDir = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false);
        $this->filesystem = $this->getMock(
            'Magento\App\Filesystem',
            array('getDirectoryWrite', 'getDirectoryRead', '__wakeup'),
            array(),
            '',
            false
        );
        $this->filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            \Magento\App\Filesystem::ROOT_DIR
        )->will(
            $this->returnValue($this->rootDirectory)
        );
        $this->filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\App\Filesystem::PUB_VIEW_CACHE_DIR
        )->will(
            $this->returnValue($this->pubViewCacheDir)
        );
        $this->adapter = $this->getMockForAbstractClass('Magento\Code\Minifier\AdapterInterface', array(), '', false);
    }

    /**
     * Test for minifyFile if case update is needed
     */
    public function testGetMinifiedFile()
    {
        $originalFile = __DIR__ . '/original/some.js';
        $minifiedFile = __DIR__ . '/minified/some.min.js';
        $content = 'content';
        $minifiedContent = 'minified content';

        $this->rootDirectory->expects(
            $this->once()
        )->method(
            'readFile'
        )->with(
            $originalFile
        )->will(
            $this->returnValue($content)
        );
        $this->pubViewCacheDir->expects($this->once())->method('getRelativePath')->will($this->returnArgument(0));
        $this->pubViewCacheDir->expects($this->once())->method('writeFile')->with($minifiedFile, $minifiedContent);

        $this->adapter->expects(
            $this->once()
        )->method(
            'minify'
        )->with(
            $content
        )->will(
            $this->returnValue($minifiedContent)
        );

        $strategy = new Generate($this->adapter, $this->filesystem);
        $strategy->minifyFile($originalFile, $minifiedFile);
    }

    /**
     * Test for minifyFile if case update is NOT needed
     */
    public function testGetMinifiedFileNoUpdateNeeded()
    {
        $originalFile = __DIR__ . '/original/some.js';
        $minifiedFile = __DIR__ . '/some.min.js';

        $mTimeMap = array(
            array($originalFile, null, array('mtime' => 1)),
            array($minifiedFile, null, array('mtime' => 1))
        );

        $this->pubViewCacheDir->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $minifiedFile
        )->will(
            $this->returnValue(true)
        );
        $this->rootDirectory->expects($this->once())->method('stat')->will($this->returnValueMap($mTimeMap));
        $this->pubViewCacheDir->expects($this->once())->method('stat')->will($this->returnValueMap($mTimeMap));

        $this->rootDirectory->expects($this->never())->method('readFile');
        $this->pubViewCacheDir->expects($this->never())->method('writeFile');

        $this->adapter->expects($this->never())->method('minify');

        $strategy = new Generate($this->adapter, $this->filesystem);
        $strategy->minifyFile($originalFile, $minifiedFile);
    }
}
