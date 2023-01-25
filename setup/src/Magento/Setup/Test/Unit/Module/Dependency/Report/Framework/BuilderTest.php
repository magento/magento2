<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Framework;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\Dependency\Report\Framework\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->builder = $objectManagerHelper->getObject(
            Builder::class
        );
    }

    /**
     * @param array $options
     * @dataProvider dataProviderWrongOptionConfigFiles
     */
    public function testBuildWithWrongOptionConfigFiles($options)
    {
        $this->expectException('InvalidArgumentException');
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
