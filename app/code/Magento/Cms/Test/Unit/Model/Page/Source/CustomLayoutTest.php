<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Page\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CustomLayoutTest extends PageLayoutTest
{
    /**
     * @return string
     */
    protected function getSourceClassName()
    {
        return 'Magento\Cms\Model\Page\Source\CustomLayout';
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
