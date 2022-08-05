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
    public function dataProviderToTestIsPresent()
    {
        return [
            [
                'sensitiveFieldList' => [],
                'environmentFieldList' => [],
                'field' => '',
                'typeList' => '',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/wrong/field',
                'typeList' => 'someWrongType',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/sensitive/field1',
                'typeList' => 'someWrongType',
                'excludeListCallback' => null,
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/wrong/field',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' => function (MockObject $mockObject) {
                    $mockObject->expects($this->once())
                        ->method('isPresent')
                        ->willReturn(false);
                },
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/environment/field1',
                'typeList' => TypePool::TYPE_ENVIRONMENT,
                'excludeListCallback' => null,
                'expectedResult' => true,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive/field1' => '1'],
                'environmentFieldList' => ['some/environment/field1' => '1'],
                'field' => 'some/environment/field1',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' =>  function (MockObject $mockObject) {
                    $mockObject->expects($this->once())
                        ->method('isPresent')
                        ->willReturn(false);
                },
                'expectedResult' => false,
            ],
            [
                'sensitiveFieldList' => ['some/sensitive-environment/field1' => '1'],
                'environmentFieldList' => ['some/sensitive-environment/field1' => '1'],
                'field' => 'some/sensitive-environment/field1',
                'typeList' => TypePool::TYPE_SENSITIVE,
                'excludeListCallback' =>  function (MockObject $mockObject) {
                    $mockObject->expects($this->never())
                        ->method('isPresent');
                },
                'expectedResult' => true,
            ],
        ];
    }
}
