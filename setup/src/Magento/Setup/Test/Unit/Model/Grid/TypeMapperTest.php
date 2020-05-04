<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\Grid\TypeMapper;
use PHPUnit\Framework\TestCase;

class TypeMapperTest extends TestCase
{
    /**
     * Model
     *
     * @var TypeMapper
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new TypeMapper();
    }

    /**
     * @param string $packageType
     * @param string $expected
     * @dataProvider mapDataProvider
     */
    public function testMap($packageType, $expected)
    {
        static::assertEquals(
            $expected,
            $this->model->map($packageType)
        );
    }

    /**
     * @return array
     */
    public function mapDataProvider()
    {
        return [
            [ComposerInformation::THEME_PACKAGE_TYPE, TypeMapper::THEME_PACKAGE_TYPE],
            [ComposerInformation::MODULE_PACKAGE_TYPE, TypeMapper::MODULE_PACKAGE_TYPE],
            ['undefined', TypeMapper::UNDEFINED_PACKAGE_TYPE]
        ];
    }
}
