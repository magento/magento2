<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\ViewModel\Product\Compare;

use Magento\Catalog\Helper\Product\Compare as CompareHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Compare Product List
 */
class Sidebar implements ArgumentInterface
{
    /**
     * @var CompareHelper
     */
    protected $compareHelper;

    /**
     * @param CompareHelper $compareHelper
     */
    public function __construct(CompareHelper $compareHelper)
    {
        $this->compareHelper = $compareHelper;
    }

    /**
     * Get parameters to clear compare list
     *
     * @return string
     */
    public function getClearCompareListParameters()
    {
        return $this->compareHelper->getPostDataClearList();
    }
}
