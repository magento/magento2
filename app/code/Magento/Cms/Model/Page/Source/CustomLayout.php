<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Model\Page\Source;

/**
 * Custom layout source
 */
class CustomLayout extends PageLayout
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_merge([['label' => 'Default', 'value' => '']], parent::toOptionArray());
    }
}
