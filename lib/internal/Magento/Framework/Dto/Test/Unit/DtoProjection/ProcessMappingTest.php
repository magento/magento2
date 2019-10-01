<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Test\Unit\DtoProjection;

use Magento\Framework\Dto\DtoProjection\ProcessMapping;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProcessMappingTest extends TestCase
{
    /**
     * @var ProcessMapping
     */
    private $processStraightMapping;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->processStraightMapping = (new ObjectManager($this))->getObject(
            ProcessMapping::class
        );
    }

    /**
     * @return void
     */
    public function testShouldMapData(): void
    {
        $source = [
            'field1' => 'value1',
            'field2' => 2,
            'field3' => [
                'sub1' => 'sub-value1',
                'sub2' => [
                    'sub-sub2' => 'sub-sub-value2',
                    'sub-sub3' => 'sub-sub-value3'
                ]
            ]
        ];

        $mapping = [
            'dst-field1' => 'field1',
            'dst-field2' => 'field2',
            'dst-field3' => 'field3',
            'dst-array.sub-value1' => 'field3.sub2.sub-sub2',
            'dst-array.sub-value2' => 'field3.sub2.non-existing-node',
            'dst-no-value' => 'non-existing-field'
        ];

        $expected = [
            'dst-field1' => $source['field1'],
            'dst-field2' => $source['field2'],
            'dst-field3' => $source['field3'],
            'dst-array' => [
                'sub-value1' => $source['field3']['sub2']['sub-sub2'],
                'sub-value2' => null,
            ],
            'dst-no-value' => null
        ];

        $res = $this->processStraightMapping->execute($source, $mapping);

        self::assertSame($expected, $res);
    }
}
