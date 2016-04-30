<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        return 'Magento\Cms\Model\Page\Source\IsActiveFilter';
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
