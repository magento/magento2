<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backup\Model;


class BackupTest extends \PHPUnit_Framework_TestCase
{

    public function testOutput()
    {
        $path = '/path/to';
        $time = 1;
        $name = 'test';
        $type = 'db';
        $extension = 'sql';
        $filename = $time . '_' . $type . '_' . $name . '.' . $extension;
        $relativePath = $path . '/' . $filename;
        $contents = 'test_content';

        $directory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $directory->expects($this->exactly(2))->method('getRelativePath')
            ->with($relativePath)->will($this->returnValue($relativePath));
        $directory->expects($this->once())->method('isFile')->with($relativePath)->will($this->returnValue(true));
        $directory->expects($this->once())->method('readFile')->with($relativePath)
            ->will($this->returnValue($contents));

        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->exactly(2))->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            ->will($this->returnValue($directory));

        $helper = $this->getMock('\Magento\Backup\Helper\Data', [], [], '', false);
        $helper->expects($this->exactly(2))->method('getExtensionByType')->with($type)
            ->will($this->returnValue($extension));

        /** @var Backup $backup */
        $backup = (new \Magento\TestFramework\Helper\ObjectManager($this))->getObject(
            'Magento\Backup\Model\Backup',
            [
                'filesystem' => $filesystem,
                'helper' => $helper
            ]
        );
        $backup->setPath($path);
        $backup->setName($name);
        $backup->setTime($time);
        $this->assertEquals($contents, $backup->output());
    }

    public function provider()
    {
        return [
            [true, ]
        ];
    }
} 