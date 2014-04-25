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
namespace Magento\Framework\Module\Dir;

class ReverseResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\Dir|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleDirs;

    protected function setUp()
    {
        $this->_moduleList = $this->getMock('Magento\Framework\Module\ModuleListInterface');
        $this->_moduleDirs = $this->getMock('Magento\Framework\Module\Dir', array(), array(), '', false, false);
        $this->_model = new \Magento\Framework\Module\Dir\ReverseResolver($this->_moduleList, $this->_moduleDirs);
    }

    /**
     * @param string $path
     * @param string $expectedResult
     * @dataProvider getModuleNameDataProvider
     */
    public function testGetModuleName($path, $expectedResult)
    {
        $this->_moduleList->expects(
            $this->once()
        )->method(
            'getModules'
        )->will(
            $this->returnValue(
                array(
                    'Fixture_ModuleOne' => array('name' => 'Fixture_ModuleOne'),
                    'Fixture_ModuleTwo' => array('name' => 'Fixture_ModuleTwo')
                )
            )
        );
        $this->_moduleDirs->expects(
            $this->atLeastOnce()
        )->method(
            'getDir'
        )->will(
            $this->returnValueMap(
                array(
                    array('Fixture_ModuleOne', '', 'app/code/Fixture/ModuleOne'),
                    array('Fixture_ModuleTwo', '', 'app/code/Fixture/ModuleTwo')
                )
            )
        );
        $this->assertSame($expectedResult, $this->_model->getModuleName($path));
    }

    public function getModuleNameDataProvider()
    {
        return array(
            'module root dir' => array('app/code/Fixture/ModuleOne', 'Fixture_ModuleOne'),
            'module root dir trailing slash' => array('app/code/Fixture/ModuleOne/', 'Fixture_ModuleOne'),
            'module root dir backward slash' => array('app/code\\Fixture\\ModuleOne', 'Fixture_ModuleOne'),
            'dir in module' => array('app/code/Fixture/ModuleTwo/etc', 'Fixture_ModuleTwo'),
            'dir in module trailing slash' => array('app/code/Fixture/ModuleTwo/etc/', 'Fixture_ModuleTwo'),
            'dir in module backward slash' => array('app/code/Fixture/ModuleTwo\\etc', 'Fixture_ModuleTwo'),
            'file in module' => array('app/code/Fixture/ModuleOne/etc/config.xml', 'Fixture_ModuleOne'),
            'file in module backward slash' => array(
                'app\\code\\Fixture\\ModuleOne\\etc\\config.xml',
                'Fixture_ModuleOne'
            ),
            'unknown module' => array('app/code/Unknown/Module', null)
        );
    }
}
