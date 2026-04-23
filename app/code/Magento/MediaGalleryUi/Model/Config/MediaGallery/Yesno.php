<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\Config\MediaGallery;

class Yesno implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() :array
    {
        return [['value' => 0, 'label' => __('Yes')], ['value' => 1, 'label' => __('No')]];
    }
}
