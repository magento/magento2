<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Filter;

use Magento\Customer\Ui\Component\Listing\Filter\TextFilterConfigProvider;
use PHPUnit\Framework\TestCase;

class TextFilterConfigProviderTest extends TestCase
{
    /**
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(array $input, array $output): void
    {
        $model = new TextFilterConfigProvider();
        $this->assertEquals($output, $model->getConfig($input));
    }

    /**
     * @return array[]
     */
    public function getConfigDataProvider(): array
    {
        return [
            [
                [],
                [
                    'conditionType' => 'like',
                    'valueExpression' => '%%%s%%'
                ]
            ],
            [
                [
                    'grid_filter_condition_type' => 0
                ],
                [
                    'conditionType' => 'like',
                    'valueExpression' => '%%%s%%'
                ]
            ],
            [
                [
                    'grid_filter_condition_type' => 1
                ],
                [
                    'conditionType' => 'like',
                    'valueExpression' => '%s%%',
                ]
            ],
            [
                [
                    'grid_filter_condition_type' => 2
                ],
                [
                    'conditionType' => 'eq',
                    'valueExpression' => null
                ]
            ]
        ];
    }
}
