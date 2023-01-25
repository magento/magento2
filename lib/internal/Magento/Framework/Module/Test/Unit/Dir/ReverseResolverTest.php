<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit\Dir;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\ReverseResolver;
use Magento\Framework\Module\ModuleListInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReverseResolverTest extends TestCase
{
    /**
     * @var ReverseResolver
     */
    protected $_model;

    /**
     * @var ModuleListInterface|MockObject
     */
    protected $_moduleList;

    /**
     * @var Dir|MockObject
     */
    protected $_moduleDirs;

    protected function setUp(): void
    {
        $this->_moduleList = $this->getMockForAbstractClass(ModuleListInterface::class);
        $this->_moduleDirs = $this->createMock(Dir::class);
        $this->_model = new ReverseResolver($this->_moduleList, $this->_moduleDirs);
    }

    /**
     * @param string $path
     * @param string $expectedResult
     * @dataProvider getModuleNameDataProvider
     */
    public function testGetModuleName($path, $expectedResult)
    {
        $this->_moduleList->expects($this->once())->method('getNames')->willReturn(
            ['Fixture_ModuleOne', 'Fixture_ModuleTwo']
        );
        $this->_moduleDirs->expects(
            $this->atLeastOnce()
        )->method(
            'getDir'
        )->willReturnMap(
            [
                ['Fixture_ModuleOne', '', 'app/code/Fixture/ModuleOne'],
                ['Fixture_ModuleTwo', '', 'app/code/Fixture/ModuleTwo'],
            ]
        );
        $this->assertSame($expectedResult, $this->_model->getModuleName($path));
    }

    /**
     * @return array
     */
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
