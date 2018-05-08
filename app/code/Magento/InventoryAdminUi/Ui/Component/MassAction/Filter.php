<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Ui\Component\MassAction;

use Magento\Ui\Component\MassAction\Filter as BaseFilter;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Temporary solution
 * @todo Need to remove after fixing the issue
 * @see https://github.com/magento/magento2/issues/10988
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
    public function getIds(): array
    {
        $this->filter->applySelectionOnTargetProvider();
        $component = $this->filter->getComponent();
        $this->filter->prepareComponent($component);

        $dataProvider = $component->getContext()->getDataProvider();
        $dataProvider->setLimit(0, false);
        $searchResult = $dataProvider->getSearchResult();

        return array_map(function (DocumentInterface $item) {
            return $item->getId();
        }, $searchResult->getItems());
    }
}
