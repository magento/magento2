<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

class IsActiveFilterTest extends IsActiveTest
{
    /**
     * {@inheritdoc}
     */
    protected function getSourceClassName()
    {
        return \Magento\Cms\Model\Page\Source\IsActiveFilter::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableStatusesDataProvider()
    {
        return [
            [
                [],
                [['label' => '', 'value' => '']],
            ],
            [
                ['testStatus' => 'testValue'],
                [['label' => '', 'value' => ''], ['label' => 'testValue', 'value' => 'testStatus']],
            ],
        ];
    }
}
