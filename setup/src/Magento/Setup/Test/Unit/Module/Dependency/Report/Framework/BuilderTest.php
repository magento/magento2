<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Dependency\Report\Framework\Builder
     */
    protected $builder;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->builder = $objectManagerHelper->getObject(
            \Magento\Setup\Module\Dependency\Report\Framework\Builder::class
        );
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongOptionConfigFiles
     */
    public function testBuildWithWrongOptionConfigFiles($options)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parse error. Passed option "config_files" is wrong.');

        $this->builder->build($options);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionConfigFiles()
    {
        return [
            [
                [
                    'parse' => ['files_for_parse' => [1, 2], 'config_files' => []],
                    'write' => [1, 2],
                ],
            ],
            [['parse' => ['files_for_parse' => [1, 2]], 'write' => [1, 2]]]
        ];
    }
}
