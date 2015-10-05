<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

class ThemeFilterTest extends ThemeTest
{
    /**
     * {@inheritdoc}
     */
    protected function getClassName()
    {
        return 'Magento\Cms\Model\Page\Source\ThemeFilter';
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        return [
            [
                [],
                [['label' => '', 'value' => '']],
            ],
            [
                [['label' => 'testValue', 'value' => 'testStatus']],
                [['label' => '', 'value' => ''], ['label' => 'testValue', 'value' => 'testStatus']],
            ],

        ];
    }
}
