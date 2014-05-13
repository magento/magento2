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
namespace Magento\Framework\Config;

use Magento\Framework\Config\FileIterator;

/**
 * Class FileIteratorTest
 */
class FileIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileIterator
     */
    protected $fileIterator;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * Array of relative file paths
     *
     * @var array
     */
    protected $filePaths;

    protected function setUp()
    {
        $this->filePaths = ['/file1', '/file2'];
        $this->directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);

        $this->fileIterator = new FileIterator(
            $this->directoryMock,
            $this->filePaths
        );
    }

    protected function tearDown()
    {
        $this->fileIterator = null;
        $this->directoryMock = null;
        $this->filePaths = null;
    }

    public function testIterator()
    {
        $contents = ['content1', 'content2'];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $this->directoryMock->expects($this->at($index))
                ->method('readFile')
                ->with($filePath)
                ->will($this->returnValue($contents[$index++]));
        }
        $index = 0;
        foreach ($this->fileIterator as $fileContent) {
            $this->assertEquals($contents[$index++], $fileContent);
        }
    }

    public function testToArray()
    {
        $contents = ['content1', 'content2'];
        $expectedArray = [];
        $index = 0;
        foreach ($this->filePaths as $filePath) {
            $expectedArray[$filePath] = $contents[$index];
            $this->directoryMock->expects($this->at($index))
                ->method('readFile')
                ->with($filePath)
                ->will($this->returnValue($contents[$index++]));
        }
        $this->assertEquals($expectedArray, $this->fileIterator->toArray());
    }
}
