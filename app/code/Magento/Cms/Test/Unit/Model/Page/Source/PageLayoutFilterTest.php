<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

class PageLayoutFilterTest extends PageLayoutTest
{
    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return 'Magento\Cms\Model\Page\Source\PageLayoutFilter';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsDataProvider()
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
