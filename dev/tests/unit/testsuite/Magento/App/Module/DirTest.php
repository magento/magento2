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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\Module;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Module\Dir
     */
    protected $_model;

    /**
     * @var \Magento\App\Dir|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_applicationDirs;

    protected function setUp()
    {
        $this->_applicationDirs = $this->getMock('Magento\App\Dir', array(), array(), '', false, false);
        $this->_applicationDirs
            ->expects($this->once())
            ->method('getDir')
            ->with(\Magento\App\Dir::MODULES)
            ->will($this->returnValue('app' . DIRECTORY_SEPARATOR . 'code'))
        ;
        $this->_model = new \Magento\App\Module\Dir($this->_applicationDirs);
    }

    public function testGetDirModuleRoot()
    {
        $this->assertEquals(
            str_replace('/', DIRECTORY_SEPARATOR, 'app/code/Test/Module'),
            $this->_model->getDir('Test_Module')
        );
    }

    public function testGetDirModuleSubDir()
    {
        $this->assertEquals(
            str_replace('/', DIRECTORY_SEPARATOR, 'app/code/Test/Module/etc'),
            $this->_model->getDir('Test_Module', 'etc')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Directory type 'unknown' is not recognized
     */
    public function testGetDirModuleSubDirUnknown()
    {
        $this->_model->getDir('Test_Module', 'unknown');
    }
}
