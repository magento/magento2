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
namespace Magento\Core\Model\Locale\Hierarchy\Config;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Locale\Hierarchy\Config\FileResolver
     */
    protected $_model;

    /**
     * @var \Magento\Filesystem\Directory\Read
     */
    protected $_directoryMock;

    /**
     * @var \Magento\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    protected function setUp()
    {
        $filesystem = $this->getMock('Magento\App\Filesystem', array('getDirectoryRead'), array(), '', false);
        $this->_directoryMock = $this->getMock(
            '\Magento\Filesystem\Directory\Read',
            array('isExist', 'search'),
            array(),
            '',
            false
        );
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\App\Filesystem::APP_DIR)
            ->will($this->returnValue($this->_directoryMock));
        $this->_directoryMock->expects($this->once())
            ->method('isExist')
            ->with('locale')
            ->will($this->returnValue(true));
        $this->iteratorFactory = $this->getMock('Magento\Config\FileIteratorFactory', array(), array(), '', false);
        $this->_model = new \Magento\Core\Model\Locale\Hierarchy\Config\FileResolver(
            $filesystem,
            $this->iteratorFactory
        );
    }

    /**
     * @covers \Magento\Core\Model\Locale\Hierarchy\Config\FileResolver::get
     */
    public function testGet()
    {
        $paths = array(
            __DIR__ . '/_files/custom/hierarchy_config.xml',
            __DIR__ . '/_files/default/hierarchy_config.xml'
        );
        $expected = array(
            0 => $paths
        );

        $this->_directoryMock->expects($this->once())
            ->method('search')
            ->will($this->returnValue(array($paths)));
        $this->iteratorFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(array($paths)));
        $this->assertEquals($expected, $this->_model->get('hierarchy_config.xml', 'scope'));
    }
}
