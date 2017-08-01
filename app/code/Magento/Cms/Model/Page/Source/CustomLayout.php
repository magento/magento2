<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page\Source;

/**
 * Custom layout source
 * @since 2.0.0
 */
class CustomLayout extends PageLayout
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return array_merge([['label' => 'Default', 'value' => '']], parent::toOptionArray());
    }
}
