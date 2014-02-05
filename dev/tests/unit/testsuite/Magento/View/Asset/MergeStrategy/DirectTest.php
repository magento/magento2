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

namespace Magento\View\Asset\MergeStrategy;

use Magento\Filesystem\Directory\Write;

class DirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Asset\MergeStrategy\Direct
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var Write | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_directory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cssUrlResolver;

    protected function setUp()
    {
        $this->_cssUrlResolver = $this->getMock('Magento\View\Url\CssResolver', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->_directory = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false);
        $this->_filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(\Magento\App\Filesystem::PUB_DIR)
            ->will($this->returnValue($this->_directory));
        $this->_directory->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));

        $this->_object = new \Magento\View\Asset\MergeStrategy\Direct(
            $this->_filesystem, $this->_cssUrlResolver
        );
    }

    /**
     * @expectedException \Magento\Exception
     * @expectedExceptionMessage Unable to locate file 'no_file.js' for merging.
     */
    public function testMergeFilesNoFilesException()
    {
        $this->_object->mergeFiles(array('no_file.js'), 'some_file.js', 'js');
    }

    /**
     * Test mergeFiles() for css content type
     */
    public function testMergeFilesCss()
    {
        $this->_cssUrlResolver
            ->expects($this->exactly(2))
            ->method('replaceCssRelativeUrls')
            ->will($this->returnArgument(0));
        $this->_testMergeFiles('css');
    }

    /**
     * Test mergeFiles() for js content type
     */
    public function testMergeFilesJs()
    {
        $this->_cssUrlResolver
            ->expects($this->never())
            ->method('replaceCssRelativeUrls');
        $this->_testMergeFiles('js');
    }

    /**
     * Test mergeFiles itself
     *
     * @param string $contentType
     */
    protected function _testMergeFiles($contentType)
    {
        $mergedFile = '/merged_file.js';

        $this->_directory
            ->expects($this->any())
            ->method('isExist')
            ->will($this->returnValue(true));

        $this->_directory->expects($this->at(3))
            ->method('readFile')
            ->with('/pub/script_one.js')
            ->will($this->returnValue('script1'));
        $this->_directory->expects($this->at(7))
            ->method('readFile')
            ->with('/pub/script_two.js')
            ->will($this->returnValue('script2'));

        $this->_directory->expects($this->once())
            ->method('writeFile')
            ->with($mergedFile, 'script1script2');

        $this->_object->mergeFiles(array('/pub/script_one.js', '/pub/script_two.js'), $mergedFile, $contentType);
    }
}
