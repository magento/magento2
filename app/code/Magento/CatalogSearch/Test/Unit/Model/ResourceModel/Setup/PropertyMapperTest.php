<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Setup;

use Magento\CatalogSearch\Model\ResourceModel\Setup\PropertyMapper;
use PHPUnit\Framework\TestCase;

class PropertyMapperTest extends TestCase
{
    /**
     * @var PropertyMapper
     */
    private $propertyMapper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->propertyMapper = new PropertyMapper();
    }

    /**
     * @return array
     */
    public static function caseProvider(): array
    {
        return [
            [
                ['search_weight' => 9, 'something_other' => '3'],
                ['search_weight' => 9]
            ],
            [
                ['something' => 3],
                ['search_weight' => 1]
            ]
        ];
    }

    /**
     * @dataProvider caseProvider
     *
     * @test
     *
     * @param array $input
     * @param array $result
     * @return void
     */
    public function testMapCorrectlyMapsValue(array $input, array $result): void
    {
        //Second parameter doesn't matter as it is not used
        $this->assertSame($result, $this->propertyMapper->map($input, 4));
    }
}
