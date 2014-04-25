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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for save method
     */
    public function testSave()
    {
        $config = array();
        $fileStorageMock = $this->getMock('Magento\Core\Model\File\Storage', array(), array(), '', false);
        $fileStorageMock->expects($this->once())->method('getScriptConfig')->will($this->returnValue($config));

        $file = $this->getMock(
            'Magento\Framework\Filesystem\File\Write',
            array('lock', 'write', 'unlock', 'close'),
            array(),
            '',
            false
        );
        $file->expects($this->once())->method('lock');
        $file->expects($this->once())->method('write')->with(json_encode($config));
        $file->expects($this->once())->method('unlock');
        $file->expects($this->once())->method('close');
        $directory = $this->getMock(
            'Magento\Framework\Filesystem\Direcoty\Write',
            array('openFile', 'getRelativePath'),
            array(),
            '',
            false
        );
        $directory->expects($this->once())->method('getRelativePath')->will($this->returnArgument(0));
        $directory->expects($this->once())->method('openFile')->with('cacheFile')->will($this->returnValue($file));
        $filesystem = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite'),
            array(),
            '',
            false
        );
        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::PUB_DIR
        )->will(
            $this->returnValue($directory)
        );
        $model = new \Magento\Core\Model\File\Storage\Config($fileStorageMock, $filesystem, 'cacheFile');
        $model->save();
    }
}
