<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

use Magento\Framework\Component\ComponentRegistrar;
use \Magento\Setup\Module\I18n\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentRegistrar;

    protected function setUp()
    {
        $this->componentRegistrar = $this->getMock(
            \Magento\Framework\Component\ComponentRegistrar::class,
            [],
            [],
            '',
            false
        );
    }

    /**
     * @param array $context
     * @param string $path
     * @param array $pathValues
     * @dataProvider dataProviderContextByPath
     */
    public function testGetContextByPath($context, $path, $pathValues)
    {
        $this->componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturnMap($pathValues);
        $this->context = new Context($this->componentRegistrar);
        $this->assertEquals($context, $this->context->getContextByPath($path));
    }

    /**
     * @return array
     */
    public function dataProviderContextByPath()
    {
        return [
            [
                [Context::CONTEXT_TYPE_MODULE, 'Magento_Module'],
                '/app/code/Magento/Module/Block/Test.php',
                [
                    [Context::CONTEXT_TYPE_MODULE, ['Magento_Module' => '/app/code/Magento/Module']],
                    [Context::CONTEXT_TYPE_THEME, []],
                ]
            ],
            [
                [Context::CONTEXT_TYPE_THEME, 'frontend/Some/theme'],
                '/app/design/area/theme/test.phtml',
                [
                    [Context::CONTEXT_TYPE_MODULE, []],
                    [Context::CONTEXT_TYPE_THEME, ['frontend/Some/theme' => '/app/design/area/theme']],
                ]
            ],
            [
                [Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml'],
                '/lib/web/module/test.phtml',
                [
                    [Context::CONTEXT_TYPE_MODULE, []],
                    [Context::CONTEXT_TYPE_THEME, []],
                ]
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid path given: "invalid_path".
     */
    public function testGetContextByPathWithInvalidPath()
    {
        $this->componentRegistrar->expects($this->any())
            ->method('getPaths')
            ->willReturnMap([
                [ComponentRegistrar::MODULE, ['/path/to/module']],
                [ComponentRegistrar::THEME, ['/path/to/theme']]
            ]);
        $this->context = new Context($this->componentRegistrar);
        $this->context->getContextByPath('invalid_path');
    }

    /**
     * @param string $path
     * @param array $context
     * @param array $registrar
     * @dataProvider dataProviderPathToLocaleDirectoryByContext
     */
    public function testBuildPathToLocaleDirectoryByContext($path, $context, $registrar)
    {
        $paths = [];
        foreach ($registrar as $module) {
            $paths[$module[1]] = $module[2];
        }
        $this->componentRegistrar->expects($this->any())
            ->method('getPath')
            ->willReturnMap($registrar);
        $this->context = new Context($this->componentRegistrar);
        $this->assertEquals($path, $this->context->buildPathToLocaleDirectoryByContext($context[0], $context[1]));
    }

    /**
     * @return array
     */
    public function dataProviderPathToLocaleDirectoryByContext()
    {
        return [
            [
                BP . '/app/code/Magento/Module/i18n/',
                [Context::CONTEXT_TYPE_MODULE, 'Magento_Module'],
                [[ComponentRegistrar::MODULE, 'Magento_Module', BP . '/app/code/Magento/Module']]
            ],
            [
                BP . '/app/design/frontend/Magento/luma/i18n/',
                [Context::CONTEXT_TYPE_THEME, 'frontend/Magento/luma'],
                [[ComponentRegistrar::THEME, 'frontend/Magento/luma', BP . '/app/design/frontend/Magento/luma']]
            ],

            [
                null,
                [Context::CONTEXT_TYPE_MODULE, 'Unregistered_Module'],
                [[ComponentRegistrar::MODULE, 'Unregistered_Module', null]]
            ],
            [
                null,
                [Context::CONTEXT_TYPE_THEME, 'frontend/Magento/unregistered'],
                [[ComponentRegistrar::THEME, 'frontend/Magento/unregistered', null]]
            ],
            [BP . '/lib/web/i18n/', [Context::CONTEXT_TYPE_LIB, 'lib/web/module/test.phtml'], []],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid context given: "invalid_type".
     */
    public function testBuildPathToLocaleDirectoryByContextWithInvalidType()
    {
        $this->componentRegistrar->expects($this->never())
            ->method('getPath');
        $this->context = new Context($this->componentRegistrar);
        $this->context->buildPathToLocaleDirectoryByContext('invalid_type', 'Magento_Module');
    }
}
