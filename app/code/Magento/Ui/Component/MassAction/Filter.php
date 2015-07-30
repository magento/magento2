<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\MassAction;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class Filter
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
     * @param UiComponentFactory $factory
     * @param RequestInterface $request
     */
    public function __construct(
        UiComponentFactory $factory,
        RequestInterface $request
    ) {
        $this->factory = $factory;
        $this->request = $request;
    }

    /**
     * @return UiComponentInterface
     * @throws LocalizedException
     */
    protected function getComponent()
    {
        $namespace = $this->request->getParam('namespace');
        if (!isset($this->components[$namespace])) {
            $this->components[$namespace] = $this->factory->create($namespace);
        }
        return $this->components[$namespace];
    }

    /**
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getCollection(AbstractDb $collection)
    {
        $component = $this->getComponent();
        $this->prepareComponent($component);
        $dataProvider = $component->getContext()->getDataProvider();
        $ids = [];
        foreach ($dataProvider->getSearchResult()->getItems() as $document) {
            $ids[] = $document->getId();
        }

        $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => $ids]);
        return $this->applySelection($collection);
    }

    /**
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
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
                $collection->addFieldToFilter($collection->getIdFieldName(), ['nin' => $excluded]);
            } elseif (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getIdFieldName(), ['in' => $selected]);
            } else {
                throw new LocalizedException(__('Please select item(s).'));
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
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }

    /**
     * Returns RefererUrl
     *
     * @return string|null
     */
    public function getComponentRefererUrl()
    {
        $data = $this->getComponent()->getContext()->getDataProvider()->getConfigData();
        return (isset($data['referer_url'])) ? $data['referer_url'] : null;
    }
}
