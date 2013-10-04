<?php
/**
 * Test for \Magento\Filesystem\Adapter\Zlib
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Adapter;

class ZlibTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Filesystem\Adapter\Zlib
     */
    protected $_adapter;

    /**
     * @var array
     */
    protected $_deleteFiles = array();

    protected function setUp()
    {
        $this->_adapter = new \Magento\Filesystem\Adapter\Zlib();
    }

    protected function tearDown()
    {
        foreach ($this->_deleteFiles as $fileName) {
            if (is_dir($fileName)) {
                rmdir($fileName);
            } elseif (is_file($fileName)) {
                unlink($fileName);
            }
        }
    }

    public function testCreateStream()
    {
        $file = $this->_getFixturesPath() . 'data.csv';
        $this->assertInstanceOf('Magento\Filesystem\Stream\Zlib', $this->_adapter->createStream($file));
    }

    public function testRW()
    {
        $file = $this->_getFixturesPath() . 'compressed.tgz';
        $this->_adapter->write($file, 'Test string');
        $this->assertFileExists($file);
        $this->_deleteFiles[] = $file;
        $this->assertEquals('Test string', $this->_adapter->read($file));
    }

    /**
     * @return string
     */
    protected function _getFixturesPath()
    {
        return __DIR__ . DS . '..' . DS . '_files' . DS;
    }
}
