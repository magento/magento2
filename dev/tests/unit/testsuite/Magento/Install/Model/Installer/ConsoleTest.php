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
namespace Magento\Install\Model\Installer;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Install\Model\Installer\Console
     */
    protected $model;
    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    protected function setUp()
    {
        $dbModelName = 'Magento\Install\Model\Installer\Db\Mysql4';
        $this->directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->directoryMock->expects(
            $this->once()
        )->method('read')->will($this->returnValue([TESTS_TEMP_DIR]));

        $this->directoryMock->expects(
            $this->any()
        )->method('isDirectory')->will($this->returnValue(true));

        $this->filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', [], [], '', false);
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->with()->will(
            $this->returnValue($this->directoryMock)
        );
        /**
         * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
         */
        $appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));

        $dbModelMock = $this->getMock($dbModelName, [], [], '', false);
        $dbModelMock->expects($this->any())->method('cleanUpDatabase')->will($this->returnValue($this));
        /**
         * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
         */
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->any())->method('get')->with($dbModelName)->will(
            $this->returnValue($dbModelMock)
        );

        $this->model = new \Magento\Install\Model\Installer\Console(
            $this->getMock('Magento\Install\Model\Installer', [], [], '', false),
            $this->getMock('Magento\Framework\App\Resource\Config', [], [], '', false),
            $this->getMock('Magento\Framework\Module\UpdaterInterface', [], [], '', false),
            $this->filesystemMock,
            $this->getMock('Magento\Install\Model\Installer\Data', [], [], '', false),
            $appStateMock,
            $this->getMock('Magento\Framework\Locale\ListsInterface', [], [], '', false),
            $objectManagerMock
        );

    }

    protected function tearDown()
    {
        $this->model = null;
    }


    public function testUninstall()
    {
        $this->directoryMock->expects($this->exactly(2))
            ->method('delete')->with($this->logicalOr($this->equalTo(TESTS_TEMP_DIR), $this->equalTo('local.xml')))
            ->will($this->returnValue(true));

        $this->assertTrue($this->model->uninstall());

    }

    public function testUninstallWithError()
    {
        $this->directoryMock->expects($this->exactly(2))
            ->method('delete')->with($this->logicalOr($this->equalTo(TESTS_TEMP_DIR), $this->equalTo('local.xml')))
            ->will(
                $this->throwException(
                    new \Magento\Framework\Filesystem\FilesystemException(sprintf(
                        'The file "%s" cannot be deleted %s',
                        TESTS_TEMP_DIR,
                        "Warning"
                    ))
                )
            );
        $expectedString = str_repeat(sprintf('Please delete the file manually : "%s" ' . "\n", TESTS_TEMP_DIR), 2);

        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->with()->will(
            $this->returnValue($this->directoryMock)
        );
        $this->expectOutputString($expectedString);
        $this->model->uninstall();

    }
}
