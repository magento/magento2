<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Config\Model\Config\TypePool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypePoolTest extends TestCase
{
    /**
     * @var ExcludeList|MockObject
     */
    private $excludeListMock;

    protected function setUp(): void
    {
        $this->excludeListMock = $this->getMockBuilder(ExcludeList::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $sensitive
     * @param array $environment
     * @param $path
     * @param $type
     * @param null|callable $excludeListCallback
     * @param $expectedResult
     * @dataProvider dataProviderToTestIsPresent
     */
    public function testIsPresent(
        array $sensitive,
        array $environment,
        $path,
        $type,
        $excludeListCallback,
        $expectedResult
    ) {
        if (is_callable($excludeListCallback)) {
            $excludeListCallback($this->excludeListMock);
        }
        $typePool = new TypePool($sensitive, $environment, $this->excludeListMock);
        $this->assertSame($expectedResult, $typePool->isPresent($path, $type));
    }

    /**
     * @return array
     */
    public static function dataProviderToTestIsPresent()
    {
        return [
            [
                'sensitive' => [],
                'environment' => [],
                'path' => '',
                'type' => '',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitive' => ['some/sensitive/field1' => '1'],
                'environment' => ['some/environment/field1' => '1'],
                'path' => 'some/wrong/field',
                'type' => 'someWrongType',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitive' => ['some/sensitive/field1' => '1'],
                'environment' => ['some/environment/field1' => '1'],
                'path' => 'some/sensitive/field1',
                'type' => 'someWrongType',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitive' => ['some/sensitive/field1' => '1'],
                'environment' => ['some/environment/field1' => '1'],
                'path' => 'some/wrong/field',
                'type' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' => function (MockObject $mockObject) {
                    $mockObject->expects(self::once())
                        ->method('isPresent')
                        ->willReturn(false);
                },
                'expectedResult' => false,
            ],
            [
                'sensitive' => ['some/sensitive/field1' => '1'],
                'environment' => ['some/environment/field1' => '1'],
                'path' => 'some/environment/field1',
                'type' => TypePool::TYPE_ENVIRONMENT,
                'excludeListCallback' => null,
                'expectedResult' => true,
            ],
            [
                'sensitive' => ['some/sensitive/field1' => '1'],
                'environment' => ['some/environment/field1' => '1'],
                'path' => 'some/environment/field1',
                'type' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' =>  function (MockObject $mockObject) {
                    $mockObject->expects(self::once())
                        ->method('isPresent')
                        ->willReturn(false);
                },
                'expectedResult' => false,
            ],
            [
                'sensitive' => ['some/sensitive-environment/field1' => '1'],
                'environment' => ['some/sensitive-environment/field1' => '1'],
                'path' => 'some/sensitive-environment/field1',
                'type' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' =>  function (MockObject $mockObject) {
                    $mockObject->expects(self::never())
                        ->method('isPresent');
                },
                'expectedResult' => true,
            ],
        ];
    }
}
