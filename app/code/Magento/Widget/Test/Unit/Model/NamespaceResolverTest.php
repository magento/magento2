<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Widget\Model\NamespaceResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NamespaceResolverTest extends TestCase
{
    /**
     * @var NamespaceResolver
     */
    protected $namespaceResolver;

    /**
     * @var ModuleListInterface|MockObject
     */
    protected $moduleListMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->getMockForAbstractClass();

        $this->namespaceResolver = $objectManager->getObject(
            NamespaceResolver::class,
            [
                'moduleList' => $this->moduleListMock
            ]
        );
    }

    /**
     * @param string $namespace
     * @param array $modules
     * @param string $expected
     * @param bool $asFullModuleName
     *
     * @dataProvider determineOmittedNamespaceDataProvider
     */
    public function testDetermineOmittedNamespace($namespace, $modules, $expected, $asFullModuleName)
    {
        $this->moduleListMock->expects($this->once())
            ->method('getNames')
            ->willReturn($modules);

        $this->assertSame(
            $expected,
            $this->namespaceResolver->determineOmittedNamespace($namespace, $asFullModuleName)
        );
    }

    /**
     * @return array
     */
    public static function determineOmittedNamespaceDataProvider()
    {
        return[
            [
                'namespace' => \Magento\Widget\Test\Unit\Model\NamespaceResolverTest::class,
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => 'Magento_Widget',
                'asFullModuleName' => true
            ],
            [
                'namespace' => \Magento\Widget\Test\Unit\Model\NamespaceResolverTest::class,
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => 'magento_widget',
                'asFullModuleName' => false
            ],
            [
                'namespace' => 'Widget\Test\Unit\Model\NamespaceResolverTest',
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => 'Magento_Widget',
                'asFullModuleName' => true

            ],
            [
                'namespace' => 'Widget\Test\Unit\Model\NamespaceResolverTest',
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => 'widget',
                'asFullModuleName' => false
            ],
            [
                'namespace' => 'Unit\Model\NamespaceResolverTest',
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => '',
                'asFullModuleName' => true
            ],
            [
                'namespace' => 'Unit\Model\NamespaceResolverTest',
                'modules' => ['Magento_Cms', 'Magento_Catalog', 'Magento_Sales', 'Magento_Widget'],
                'expected' => '',
                'asFullModuleName' => false
            ],
        ];
    }
}
