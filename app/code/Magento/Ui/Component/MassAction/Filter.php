<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\MassAction;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Filter component.
 *
 * @api
 * @since 100.0.2
 */
class Filter
{
    const SELECTED_PARAM = 'selected';

    const EXCLUDED_PARAM = 'excluded';

    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UiComponentInterface[]
     */
    protected $components = [];

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @param UiComponentFactory $factory
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        UiComponentFactory $factory,
        RequestInterface $request,
        FilterBuilder $filterBuilder
    ) {
        $this->factory = $factory;
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Returns component by namespace
     *
     * @return UiComponentInterface
     * @throws LocalizedException
     */
    public function getComponent()
    {
        $namespace = $this->request->getParam('namespace');
        if (!isset($this->components[$namespace])) {
            $this->components[$namespace] = $this->factory->create($namespace);
        }
        return $this->components[$namespace];
    }

    /**
     * Adds filters to collection using DataProvider filter results
     *
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getCollection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);

        $isExcludedIdsValid = (is_array($excluded) && !empty($excluded));
        $isSelectedIdsValid = (is_array($selected) && !empty($selected)) || ($selected === 'all');

        if ('false' !== $excluded) {
            if (!$isExcludedIdsValid && !$isSelectedIdsValid) {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        }

        $filterIds = $this->getFilterIds();
        if (\is_array($selected)) {
            $filterIds = array_unique(array_intersect($filterIds, $selected));
        }
        $collection->addFieldToFilter(
            $collection->getResource()->getIdFieldName(),
            ['in' => $filterIds]
        );

        return $collection;
    }

    /**
     * Apply selection by Excluded Included to Search Result
     *
     * @return void
     * @throws LocalizedException
     */
    public function applySelectionOnTargetProvider()
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);
        if ('false' === $excluded) {
            return;
        }
        $dataProvider = $this->getDataProvider();
        try {
            if (is_array($excluded) && !empty($excluded)) {
                $this->filterBuilder->setConditionType('nin')
                    ->setField($dataProvider->getPrimaryFieldName())
                    ->setValue($excluded);
                $dataProvider->addFilter($this->filterBuilder->create());
            } elseif (is_array($selected) && !empty($selected)) {
                $this->filterBuilder->setConditionType('in')
                    ->setField($dataProvider->getPrimaryFieldName())
                    ->setValue($selected);
                $dataProvider->addFilter($this->filterBuilder->create());
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Applies selection to collection from POST parameters
     *
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     * @deprecated Unused method. Avoid to use this method in custom code
     *
     */
    protected function applySelection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);

        if ('false' === $excluded) {
            return $collection;
        }

        try {
            if (is_array($excluded) && !empty($excluded)) {
                $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), ['nin' => $excluded]);
            } elseif (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), ['in' => $selected]);
            } else {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $collection;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }

    /**
     * Returns Referrer Url
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function getComponentRefererUrl()
    {
        $data = $this->getComponent()->getContext()->getDataProvider()->getConfigData();
        return (isset($data['referer_url'])) ? $data['referer_url'] : null;
    }

    /**
     * Get data provider
     *
     * @return DataProviderInterface
     * @throws LocalizedException
     */
    private function getDataProvider()
    {
        if (!$this->dataProvider) {
            $component = $this->getComponent();
            $this->prepareComponent($component);
            $this->dataProvider = $component->getContext()->getDataProvider();
        }
        return $this->dataProvider;
    }

    /**
     * Get filter ids as array
     *
     * @return int[]
     * @throws LocalizedException
     */
    private function getFilterIds()
    {
        $idsArray = [];
        $this->applySelectionOnTargetProvider();
        if ($this->getDataProvider() instanceof \Magento\Ui\DataProvider\AbstractDataProvider) {
            // Use collection's getAllIds for optimization purposes.
            $idsArray = $this->getDataProvider()->getAllIds();
        } else {
            $dataProvider = $this->getDataProvider();
            $dataProvider->setLimit(0, false);
            $searchResult = $dataProvider->getSearchResult();
            // Use compatible search api getItems when searchResult is not a collection.
            foreach ($searchResult->getItems() as $item) {
                /** @var $item \Magento\Framework\Api\Search\DocumentInterface */
                $idsArray[] = $item->getId();
            }
        }
        return $idsArray;
    }
}
