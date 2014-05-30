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

namespace Magento\Framework\App\View\Deployment\Version\Storage;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directory;

    protected function setUp()
    {
        $this->directory = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with('fixture_dir')
            ->will($this->returnValue($this->directory))
        ;
        $this->object = new File($filesystem, 'fixture_dir', 'fixture_file.txt');
    }

    public function testLoad()
    {
        $this->directory
            ->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->will($this->returnValue('123'))
        ;
        $this->assertEquals('123', $this->object->load());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Exception to be propagated
     */
    public function testLoadExceptionPropagation()
    {
        $this->directory
            ->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->will($this->throwException(new \Exception('Exception to be propagated')))
        ;
        $this->object->load();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unable to retrieve deployment version of static files from the file system
     */
    public function testLoadExceptionWrapping()
    {
        $filesystemException = new \Magento\Framework\Filesystem\FilesystemException('File does not exist');
        $this->directory
            ->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->will($this->throwException($filesystemException))
        ;
        try {
            $this->object->load();
        } catch (\Exception $e) {
            $this->assertSame($filesystemException, $e->getPrevious(), 'Wrapping of original exception is expected');
            throw $e;
        }
    }

    public function testSave()
    {
        $this->directory
            ->expects($this->once())
            ->method('writeFile')
            ->with('fixture_file.txt', 'input_data', 'w')
        ;
        $this->object->save('input_data');
    }
}
