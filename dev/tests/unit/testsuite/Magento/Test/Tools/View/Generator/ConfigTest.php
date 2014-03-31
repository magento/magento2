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
namespace Magento\Test\Tools\View\Generator;


require_once __DIR__ . '/../../../../../../../../tools/Magento/Tools/View/Generator/Config.php';
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    protected function setUp()
    {
        $this->_rootDirectory = $this->getMockForAbstractClass('Magento\Filesystem\Directory\WriteInterface');
        $this->_rootDirectory->expects($this->never())->method('getAbsolutePath');
        $this->_filesystem = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->_rootDirectory)
        );
    }

    /**
     * @param array $cmdOptions
     * @param array $allowedFiles
     * @param array $relativePathsMap
     * @param array $dirExistenceMap
     * @param bool $expectsReadingDir
     * @param string $exceptionMessage
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(
        array $cmdOptions,
        array $relativePathsMap,
        array $dirExistenceMap,
        array $allowedFiles,
        $expectsReadingDir,
        $exceptionMessage
    ) {
        if ($exceptionMessage) {
            $this->setExpectedException('\Magento\Exception', $exceptionMessage);
        }

        $this->_rootDirectory->expects(
            $this->any()
        )->method(
            'getRelativePath'
        )->will(
            $this->returnValueMap($relativePathsMap)
        );
        $this->_rootDirectory->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->will(
            $this->returnValueMap($dirExistenceMap)
        );

        if ($expectsReadingDir) {
            $this->_rootDirectory->expects(
                $this->once()
            )->method(
                'read'
            )->will(
                $this->returnValue(array('destination/one', 'destination/two'))
            );
        }

        new \Magento\Tools\View\Generator\Config($this->_filesystem, $cmdOptions, $allowedFiles);
    }

    public function constructorDataProvider()
    {
        $sourceDir = '/base/dir/source';
        $relativeSourceDir = 'source';
        $destinationDir = '/base/dir/destination';
        $relativeDestinationDir = 'destination';

        return array(
            'exception: non-empty destination dir' => array(
                array('source' => $sourceDir, 'destination' => $destinationDir),
                array(array($sourceDir, $relativeSourceDir), array($destinationDir, $relativeDestinationDir)),
                array(array($relativeSourceDir, true), array($relativeDestinationDir, true)),
                array('one'),
                true,
                'Destination directory must be empty'
            ),
            'exception: nonexistent destination directory' => array(
                array('source' => $sourceDir, 'destination' => $destinationDir),
                array(array($sourceDir, $relativeSourceDir), array($destinationDir, $relativeDestinationDir)),
                array(array($relativeSourceDir, true), array($relativeDestinationDir, false)),
                array(),
                false,
                'Destination directory does not exist'
            ),
            'exception: nonexistent source directory' => array(
                array('source' => $sourceDir),
                array(array($sourceDir, $relativeSourceDir)),
                array(array($relativeSourceDir, false)),
                array(),
                false,
                'Source directory does not exist'
            ),
            'no exception' => array(
                array('source' => $sourceDir, 'destination' => $destinationDir),
                array(array($sourceDir, $relativeSourceDir), array($destinationDir, $relativeDestinationDir)),
                array(array($relativeSourceDir, true), array($relativeDestinationDir, true)),
                array('one', 'two'),
                true,
                null
            )
        );
    }
}
