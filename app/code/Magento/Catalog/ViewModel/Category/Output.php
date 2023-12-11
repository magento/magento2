<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Category;

use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Category;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Category attribute output view model
 */
class Output implements ArgumentInterface
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * Initialize dependencies.
     *
     * @param OutputHelper $outputHelper
     */
    public function __construct(OutputHelper $outputHelper)
    {
        $this->outputHelper = $outputHelper;
    }

    /**
     * Prepare category attribute html output
     *
     * @param Category $category
     * @param string $attributeHtml
     * @param string $attributeName
     * @return string
     */
    public function categoryAttribute(Category $category, string $attributeHtml, string $attributeName): string
    {
        return $this->outputHelper->categoryAttribute($category, $attributeHtml, $attributeName);
    }
}
