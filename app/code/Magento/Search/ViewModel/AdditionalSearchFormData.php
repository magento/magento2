<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for search additional form params
 */
class AdditionalSearchFormData implements ArgumentInterface
{
    /**
     * Return search query params
     *
     * @return array
     */
    public function getFormData(): array
    {
        return [];
    }
}
