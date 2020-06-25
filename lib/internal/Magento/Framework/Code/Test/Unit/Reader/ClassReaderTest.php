<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit\Reader;

use Magento\Framework\Code\Reader\ClassReader;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ClassesForArgumentsReader.php';

class ClassReaderTest extends TestCase
{

    /**
     * @var ClassReader $model
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->model = new ClassReader();
    }

    /**
     * Get constructor test
     *
     * @param array $testData
     * @dataProvider getTestData
     * @throws \ReflectionException
     */
    public function testGetConstructor(array $testData)
    {
        $this->assertEquals(
            $testData,
            $this->model->getConstructor('FirstClassForParentCall')
        );
    }

    /**
     * Ensure that if we have cached class then returns this class
     */
    public function testGetParents()
    {
        $model = new ClassReader();
        $this->assertEquals([0 => 'FirstClassForParentCall'], $model->getParents('ThirdClassForParentCall'));
        $reflection = new \ReflectionClass(ClassReader::class);
        $expectedClass = $reflection->getProperty('parentsCache');
        $expectedClass->setAccessible(true);
        $this->assertEquals(
            $expectedClass->getValue($model)['ThirdClassForParentCall'],
            $model->getParents('ThirdClassForParentCall')
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getTestData()
    {
        return
            [
                [
                    [
                        0 => [
                            0 => 'stdClassObject',
                            1 => 'stdClass',
                            2 => true,
                            3 => null,
                            4 => false,
                        ],
                        1 => [
                            0 => 'runeTimeException',
                            1 => 'ClassExtendsDefaultPhpType',
                            2 => true,
                            3 => null,
                            4 => false
                        ],
                        2 => [
                            0 => 'arrayVariable',
                            1 => null,
                            2 => false,
                            3 => [
                                'key' => 'value',
                            ],
                            4 => false
                        ]
                    ]
                ]
            ];
    }
}
