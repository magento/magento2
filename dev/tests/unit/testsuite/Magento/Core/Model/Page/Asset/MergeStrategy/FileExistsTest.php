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

class FileExistsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Page\Asset\MergeStrategy\FileExists
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_strategy;

    /**
     * @var string
     */
    protected $_mergedFile = 'destination_file.js';

    /**
     * @var array
     */
    protected $_filesArray = array('file1.js', 'file2.js');

    protected function setUp()
    {
        $this->_filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $this->_strategy = $this->getMock('Magento\Core\Model\Page\Asset\MergeStrategyInterface');

        $this->_object = new \Magento\Core\Model\Page\Asset\MergeStrategy\FileExists(
            $this->_strategy,
            $this->_filesystem
        );
    }

    public function testMergeFilesFileExists()
    {
        $this->_strategy
            ->expects($this->never())
            ->method('mergeFiles')
        ;

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($this->_mergedFile)
            ->will($this->returnValue(true))
        ;

        $this->_object->mergeFiles($this->_filesArray, $this->_mergedFile, 'contentType');
    }

    public function testMergeFilesFileDoesNotExist()
    {
        $this->_strategy
            ->expects($this->once())
            ->method('mergeFiles')
            ->with($this->_filesArray, $this->_mergedFile, 'contentType')
        ;

        $this->_filesystem->expects($this->once())
            ->method('has')
            ->with($this->_mergedFile)
            ->will($this->returnValue(false))
        ;

        $this->_object->mergeFiles($this->_filesArray, $this->_mergedFile, 'contentType');
    }
}
