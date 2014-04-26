<?php
/**
 *
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
namespace Magento\Framework\App\Arguments;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Loader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirs;

    public function setUp()
    {
        $this->_dirs = $this->getMock(
            '\Magento\Framework\App\Filesystem\DirectoryList',
            array('getDir'),
            array(),
            '',
            false
        );
    }

    public function testWithOneXmlFile()
    {
        $this->_dirs->expects($this->once())->method('getDir')->will($this->returnValue(__DIR__ . '/_files'));
        $this->_model = new Loader($this->_dirs);
        $expected = require __DIR__ . '/_files/local.php';
        $this->assertEquals($expected, $this->_model->load());
    }

    public function testWithTwoXmlFileMerging()
    {
        $this->_dirs->expects($this->once())->method('getDir')->will($this->returnValue(__DIR__ . '/_files'));
        $this->_model = new Loader($this->_dirs, 'other/local_developer.xml');
        $expected = require __DIR__ . '/_files/other/local_developer_merged.php';
        $this->assertEquals($expected, $this->_model->load());
    }

    public function testWithoutXmlFiles()
    {
        $this->_dirs->expects($this->once())->method('getDir')->will($this->returnValue(__DIR__ . '/notExistFolder'));
        $this->_model = new Loader($this->_dirs);
        $this->assertEquals(array(), $this->_model->load());
    }
}
