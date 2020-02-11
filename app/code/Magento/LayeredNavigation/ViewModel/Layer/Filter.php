<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\ViewModel\Layer;

use Magento\Catalog\Helper\Data as DataHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Product Layer
 */
class Filter implements ArgumentInterface
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * Constructor
     *
     * @param DataHelper $dataHelper
     */
    public function __construct(DataHelper $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Check is should display product count on layer
     *
     * @return bool
     */
    public function shouldDisplayProductCountOnLayer(): bool
    {
        return $this->dataHelper->shouldDisplayProductCountOnLayer();
    }
}
