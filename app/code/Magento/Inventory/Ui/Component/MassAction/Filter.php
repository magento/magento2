<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Ui\Component\MassAction;

use Magento\Ui\Component\MassAction\Filter as BaseFilter;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Class Filter
 * @todo remove this class after code from Engine\MagentoFix\Ui\Component\MassAction\Filter would be deploy to
 *       production
 */
class Filter
{
    /**
     * @var BaseFilter
     */
    private $filter;

    /**
     * @param BaseFilter $filter
     */
    public function __construct(
        BaseFilter $filter
    ) {
        $this->filter = $filter;
    }

    /**
     * Get ids from search filter
     *
     * @return array
     */
    public function getIds()
    {
        $this->filter->applySelectionOnTargetProvider();

        return array_map(function(DocumentInterface $item) {
            return $item->getId();
        }, $this->filter->getComponent()->getContext()->getDataProvider()->getSearchResult()->getItems());
    }
}