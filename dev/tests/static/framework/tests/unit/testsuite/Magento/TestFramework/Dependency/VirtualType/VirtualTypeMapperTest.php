<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency\VirtualType;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class VirtualTypeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VirtualTypeMapper
     */
    private $mapper;

    protected function setUp(): void
    {
        $managerHelper = new ObjectManager($this);
        $this->mapper = $managerHelper->getObject(VirtualTypeMapper::class, [
            'map' => [
                'global' => [
                    'virtualType1' => 'Magento\SomeModule\Some\Class1',
                    'virtualType2' => 'Magento\SomeModule\Some\Class2',
                    'virtualType3' => 'Magento\SomeModule\Some\Class3',
                ],
                'adminhtml' => [
                    'virtualType1' => 'Magento\SomeModule\Some\Class4',
                    'virtualType4' => 'Magento\SomeModule\Some\Class5',
                ]
            ]
        ]);
    }

    public function testGetScopeFromFile()
    {
        $file = '/path/to/file/scope/filename.ext';
        static::assertEquals('scope', $this->mapper->getScopeFromFile($file));
    }

    /**
     * @param string $scope
     * @param string $type
     * @param string $expected
     * @dataProvider getTypeDataProvider
     */
    public function testGetType($scope, $type, $expected)
    {
        static::assertEquals($expected, $this->mapper->getType($type, $scope));
    }

    /**
     * @return array
     */
    public static function getTypeDataProvider()
    {
        return [
            [
                'global',
                'virtualType1',
                'Magento\SomeModule\Some\Class1'
            ],
            [
                'global',
                'virtualType2',
                'Magento\SomeModule\Some\Class2'
            ],
            [
                'adminhtml',
                'virtualType3',
                'Magento\SomeModule\Some\Class3'
            ],
            [
                'adminhtml',
                'virtualType1',
                'Magento\SomeModule\Some\Class4'
            ],
            [
                'adminhtml',
                'virtualType4',
                'Magento\SomeModule\Some\Class5'
            ]
        ];
    }

    /**
     * @param array $diFilesPath
     * @param array $expectedVirtualTypesDependencies
     * @dataProvider loadConfigurationDataProvider
     */
    public function testLoad(array $diFilesPath, array $expectedVirtualTypesDependencies)
    {
        $mapper = new VirtualTypeMapper();
        self::assertArrayEqualsRecursive(
            $expectedVirtualTypesDependencies,
            $mapper->loadMap($diFilesPath)
        );
    }

    /**
     * @param array $diFilesPath
     * @param array $expectedVirtualTypesDependencies
     * @dataProvider loadConfigurationDataProvider
     */
    public function testGetTypeComplex(array $diFilesPath, array $expectedVirtualTypesDependencies)
    {
        $mapper = new VirtualTypeMapper();
        $mapper->loadMap($diFilesPath);

        // getType will return input value in case there no virtualType with the same name was found
        $expectedVirtualTypesDependencies['global']['ConcreteClass'] = 'ConcreteClass';

        foreach ($expectedVirtualTypesDependencies as $scope => $deps) {
            foreach ($deps as $virtualType => $baseType) {
                self::assertEquals($baseType, $mapper->getType($virtualType, $scope));
            }
        }
    }

    /**
     * @return array
     */
    public static function loadConfigurationDataProvider()
    {
        return [
            // collects two virtual types, defined in module configuration on global area level
            [
                'diFilesPath' => [self::getFilePath('etc/di.xml')],
                'expectedVirtualTypesDependencies' => [
                    'global' => [
                        'Magento\Internal\Some\Class' => 'ExternalVirtualType',
                        'MyVirtualType' => 'Magento\Internal\Some\Class'
                    ]
                ]
            ],

            /**
             * expectation is the same for global area,
             * but extended for adminhtml as it contains own virtual types definitions
             */
            [
                'diFilesPath' => [self::getFilePath('etc/di.xml'), self::getFilePath('etc/adminhtml/di.xml')],
                'expectedVirtualTypesDependencies' => [
                    'global' => [
                        'Magento\Internal\Some\Class' => 'ExternalVirtualType',
                        'MyVirtualType' => 'Magento\Internal\Some\Class'
                    ],
                    'adminhtml' => [
                        'MyVirtualType2' => 'ExternalVirtualType'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $expectation
     * @param array $input
     */
    private static function assertArrayEqualsRecursive(
        array $expectation,
        array $input
    ) {
        static::assertEquals(count($expectation), count($input));
        foreach ($expectation as $ek => $ev) {
            self::assertArrayHasKey($ek, $input);
            if (is_array($ev)) {
                self::assertArrayEqualsRecursive($ev, $input[$ek]);
            } else {
                self::assertEquals($ev, $input[$ek]);
            }
        }
    }

    /**
     * @param string $fileName
     * @return string
     */
    private static function getFilePath($fileName)
    {
        return __DIR__
        . DIRECTORY_SEPARATOR
        . '_files' . DIRECTORY_SEPARATOR . $fileName;
    }
}
