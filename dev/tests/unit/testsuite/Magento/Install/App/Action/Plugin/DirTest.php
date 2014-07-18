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
namespace Magento\Install\App\Action\Plugin;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dir plugin
     *
     * @var \Magento\Install\App\Action\Plugin\Dir
     */
    protected $plugin;

    /**
     * App state mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    protected $appStateMock;

    /**
     * Var directory
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Write
     */
    protected $varDirectory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->appStateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $filesystem =
            $this->getMock('Magento\Framework\App\Filesystem', array('getDirectoryWrite'), array(), '', false);
        $this->varDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('read', 'isDirectory', 'delete'),
            array(),
            '',
            false
        );
        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::VAR_DIR
        )->will(
            $this->returnValue($this->varDirectory)
        );
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->subjectMock = $this->getMock('Magento\Install\Controller\Index\Index', array(), array(), '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->plugin = new \Magento\Install\App\Action\Plugin\Dir($this->appStateMock, $filesystem, $logger);
    }

    /**
     * Test when app is installed
     */
    public function testBeforeDispatchWhenAppIsInstalled()
    {
        $directories = array('dir1', 'dir2');
        $this->appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(false));
        $this->varDirectory->expects($this->once())->method('read')->will($this->returnValue($directories));
        $this->varDirectory->expects(
            $this->exactly(count($directories))
        )->method(
            'isDirectory'
        )->will(
            $this->returnValue(true)
        );
        $this->varDirectory->expects($this->exactly(count($directories)))->method('delete');
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }

    /**
     * Test when app is not installed
     */
    public function testBeforeDispatchWhenAppIsNotInstalled()
    {
        $this->appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->varDirectory->expects($this->never())->method('read');
        $this->plugin->beforeDispatch($this->subjectMock, $this->requestMock);
    }
}
