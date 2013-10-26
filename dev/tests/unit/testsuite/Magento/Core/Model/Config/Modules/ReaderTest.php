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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Config\Modules\File
 */
namespace Magento\Core\Model\Config\Modules;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    protected function setUp()
    {
        $this->_protFactoryMock = $this->getMock('Magento\Core\Model\Config\BaseFactory',
            array(), array(), '', false, false);
        $this->_dirsMock = $this->getMock('Magento\App\Module\Dir', array(), array(), '', false, false);
        $this->_baseConfigMock = $this->getMock('Magento\Core\Model\Config\Base', array(), array(), '', false, false);
        $this->_moduleListMock = $this->getMock('Magento\App\ModuleListInterface');

        $this->_model = new \Magento\Core\Model\Config\Modules\Reader(
            $this->_dirsMock,
            $this->_protFactoryMock,
            $this->_moduleListMock
        );
    }

    public function testLoadModulesConfiguration()
    {
        $modulesConfig = array('mod1' => array());
        $fileName = 'acl.xml';
        $this->_protFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with($this->equalTo('<config/>'))
            ->will($this->returnValue($this->_baseConfigMock));

        $this->_moduleListMock->expects($this->once())
            ->method('getModules')
            ->will($this->returnValue($modulesConfig));

        $result = $this->_model->loadModulesConfiguration($fileName, null, null, array());
        $this->assertInstanceOf('Magento\Core\Model\Config\Base', $result);
    }

    public function testLoadModulesConfigurationMergeToObject()
    {
        $fileName = 'acl.xml';
        $mergeToObject = $this->getMock('Magento\Core\Model\Config\Base', array(), array(), '', false, false);
        $mergeModel = null;
        $modulesConfig = array('mod1' => array());

        $this->_moduleListMock->expects($this->once())
            ->method('getModules')
            ->will($this->returnValue($modulesConfig));

        $this->_protFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('<config/>'))
            ->will($this->returnValue($mergeToObject));

        $this->_model->loadModulesConfiguration($fileName, $mergeToObject, $mergeModel);
    }

    public function testGetModuleDir()
    {
        $this->_dirsMock->expects($this->any())
            ->method('getDir')
            ->with('Test_Module', 'etc')
            ->will($this->returnValue('app/code/Test/Module/etc'));
        $this->assertEquals('app/code/Test/Module/etc', $this->_model->getModuleDir('etc', 'Test_Module'));
    }
}
