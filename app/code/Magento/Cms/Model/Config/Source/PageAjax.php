<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Empty source model for CMS page select with AJAX search
 *
 * This source model returns an empty array as options are loaded via AJAX.
 * It's used together with CmsPageSelect frontend model.
 */
class PageAjax implements OptionSourceInterface
{
    /**
     * Return empty array - options are loaded via AJAX search
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [];
    }
}
