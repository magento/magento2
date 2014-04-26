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
namespace Magento\Framework\Autoload;

class ClassMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Autoload\ClassMap
     */
    protected $_loader = null;

    protected function setUp()
    {
        $this->_loader = new \Magento\Framework\Autoload\ClassMap(__DIR__ . '/ClassMapTest');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructNonExistent()
    {
        new \Magento\Framework\Autoload\ClassMap('non_existent');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructNotDir()
    {
        new \Magento\Framework\Autoload\ClassMap(__FILE__);
    }

    public function testGetFileAddMap()
    {

        $this->assertFalse($this->_loader->getFile('TestMap'));
        $this->assertFalse($this->_loader->getFile('Non_Existent_Class'));
        $this->assertSame($this->_loader, $this->_loader->addMap(array('TestMap' => 'TestMap.php')));
        $this->assertFileExists($this->_loader->getFile('TestMap'));
        $this->assertFalse($this->_loader->getFile('Non_Existent_Class'));
    }

    public function testLoad()
    {
        $this->_loader->addMap(array('TestMap' => 'TestMap.php', 'Unknown_Class' => 'invalid_file.php'));
        $this->assertFalse(class_exists('TestMap', false));
        $this->assertFalse(class_exists('Unknown_Class', false));
        $this->_loader->load('TestMap');
        $this->_loader->load('Unknown_Class');
        $this->assertTrue(class_exists('Magento\Framework\Autoload\ClassMapTest\TestMap', false));
        $this->assertFalse(class_exists('Unknown_Class', false));
    }
}
