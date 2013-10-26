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

namespace Magento\Core\Model\Page\Asset\MergeStrategy;

class DirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Page\Asset\MergeStrategy\Direct
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cssHelper;

    protected function setUp()
    {
        $this->_cssHelper = $this->getMock('Magento\Core\Helper\Css', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $this->_dirs = $this->getMock('Magento\App\Dir', array(), array(), '', false);

        $this->_object = new \Magento\Core\Model\Page\Asset\MergeStrategy\Direct(
            $this->_filesystem, $this->_dirs, $this->_cssHelper
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
        $this->_cssHelper
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
        $this->_cssHelper
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

        $this->_filesystem->expects($this->exactly(2))
            ->method('has')
            ->will($this->returnValueMap(
                array(
                    array('/pub/script_one.js', null, true),
                    array('/pub/script_two.js', null, true),
                )
            ));

        $this->_filesystem->expects($this->exactly(2))
            ->method('read')
            ->will($this->returnValueMap(
                array(
                    array('/pub/script_one.js', null, 'script1'),
                    array('/pub/script_two.js', null, 'script2'),
                )
            ));

        $this->_filesystem
            ->expects($this->once())
            ->method('setIsAllowCreateDirectories')
            ->with(true);

        $this->_filesystem->expects($this->once())
            ->method('write')
            ->with($mergedFile, 'script1script2')
        ;

        $this->_object->mergeFiles(array('/pub/script_one.js', '/pub/script_two.js'), $mergedFile, $contentType);
    }
}
