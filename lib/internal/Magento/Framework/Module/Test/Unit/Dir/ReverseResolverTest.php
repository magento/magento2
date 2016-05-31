<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit\Dir;

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
        $this->_moduleDirs = $this->getMock('Magento\Framework\Module\Dir', [], [], '', false, false);
        $this->_model = new \Magento\Framework\Module\Dir\ReverseResolver($this->_moduleList, $this->_moduleDirs);
    }

    /**
     * @param string $path
     * @param string $expectedResult
     * @dataProvider getModuleNameDataProvider
     */
    public function testGetModuleName($path, $expectedResult)
    {
        $this->_moduleList->expects($this->once())->method('getNames')->will(
            $this->returnValue(['Fixture_ModuleOne', 'Fixture_ModuleTwo'])
        );
        $this->_moduleDirs->expects(
            $this->atLeastOnce()
        )->method(
            'getDir'
        )->will(
            $this->returnValueMap(
                [
                    ['Fixture_ModuleOne', '', 'app/code/Fixture/ModuleOne'],
                    ['Fixture_ModuleTwo', '', 'app/code/Fixture/ModuleTwo'],
                ]
            )
        );
        $this->assertSame($expectedResult, $this->_model->getModuleName($path));
    }

    public function getModuleNameDataProvider()
    {
        return [
            'module root dir' => ['app/code/Fixture/ModuleOne', 'Fixture_ModuleOne'],
            'module root dir trailing slash' => ['app/code/Fixture/ModuleOne/', 'Fixture_ModuleOne'],
            'module root dir backward slash' => ['app/code\\Fixture\\ModuleOne', 'Fixture_ModuleOne'],
            'dir in module' => ['app/code/Fixture/ModuleTwo/etc', 'Fixture_ModuleTwo'],
            'dir in module trailing slash' => ['app/code/Fixture/ModuleTwo/etc/', 'Fixture_ModuleTwo'],
            'dir in module backward slash' => ['app/code/Fixture/ModuleTwo\\etc', 'Fixture_ModuleTwo'],
            'file in module' => ['app/code/Fixture/ModuleOne/etc/config.xml', 'Fixture_ModuleOne'],
            'file in module backward slash' => [
                'app\\code\\Fixture\\ModuleOne\\etc\\config.xml',
                'Fixture_ModuleOne',
            ],
            'unknown module' => ['app/code/Unknown/Module', null]
        ];
    }
}
