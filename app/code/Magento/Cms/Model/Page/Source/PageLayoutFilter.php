<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page\Source;

/**
 * Page layout filter source
 * @since 2.0.0
 */
class PageLayoutFilter extends PageLayout
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return array_merge([['label' => '', 'value' => '']], parent::toOptionArray());
    }
}
