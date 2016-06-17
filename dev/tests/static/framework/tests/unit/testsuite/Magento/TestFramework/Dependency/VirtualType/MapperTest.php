<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency\VirtualType;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $managerHelper = new ObjectManager($this);
        $this->mapper = $managerHelper->getObject(Mapper::class, [
            'map' => [
                'etc' => [
                    'virtualType1' => 'Magento\SomeModule\Some\Class1',
                    'virtualType2' => 'Magento\SomeModule\Some\Class2',
                    'virtualType3' => 'Magento\SomeModule\Some\Class3',
                ],
                'admin' => [
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
    public function getTypeDataProvider()
    {
        return [
            [
                'etc',
                'virtualType1',
                'Magento\SomeModule\Some\Class1'
            ],
            [
                'etc',
                'virtualType2',
                'Magento\SomeModule\Some\Class2'
            ],
            [
                'admin',
                'virtualType3',
                'Magento\SomeModule\Some\Class3'
            ],
            [
                'admin',
                'virtualType1',
                'Magento\SomeModule\Some\Class4'
            ],
            [
                'admin',
                'virtualType4',
                'Magento\SomeModule\Some\Class5'
            ]
        ];
    }
}
