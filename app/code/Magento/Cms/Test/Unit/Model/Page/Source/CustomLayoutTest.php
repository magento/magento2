<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Cms\Model\Page\Source\CustomLayout;

class CustomLayoutTest extends PageLayoutTest
{
    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return CustomLayout::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsDataProvider()
    {
        return [
            [
                [],
                [['label' => 'Default', 'value' => '']],
            ],
            [
                ['testStatus' => 'testValue'],
                [['label' => 'Default', 'value' => ''], ['label' => 'testValue', 'value' => 'testStatus']],
            ],
        ];
    }
}
