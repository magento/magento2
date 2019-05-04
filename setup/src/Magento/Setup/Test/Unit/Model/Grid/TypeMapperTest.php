<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\Grid\TypeMapper;

/**
 * Class TypeMapperTest
 */
class TypeMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Model
     *
     * @var TypeMapper
     */
    private $model;

    public function setUp()
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
