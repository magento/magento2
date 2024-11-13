<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see \Magento\MediaGallery\Model\Directory\IsExcluded.
 */
class IsExcludedTest extends TestCase
{
    /**
     * @var IsExcluded
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(IsExcluded::class);
    }

    /**
     * @dataProvider directoriesDataProvider
     * @param string $path
     * @param bool $expectedResult
     * @return void
     */
    public function testIsExcluded(string $path, bool $expectedResult): void
    {
        $actualResult = $this->model->execute($path);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function directoriesDataProvider(): array
    {
        return [
            [
                'catalog',
                true
            ],
            [
                'catalog/category',
                false
            ],
            [
                'customer',
                true
            ],
            [
                'catalog/../customer',
                true
            ],
        ];
    }
}
