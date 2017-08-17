<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\System\Store;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Visibility
 *
 * @api
 * @since 100.1.0
 */
class Visibility extends Column
{
    /**
     * @var Store
     * @since 100.1.0
     */
    protected $store;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Store $store
     * @param array $components
     * @param array $data
     * @since 100.1.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Store $store,
        array $components,
        array $data
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!empty($item['stores'])) {
                $item['visibility'] = $this->renderVisibilityStructure($item['stores']);
            }
        }

        return $dataSource;
    }

    /**
     * Rendering store visibility structure
     *
     * @param array $storeIds
     * @return string
     * @since 100.1.0
     */
    protected function renderVisibilityStructure(array $storeIds)
    {
        $visibility = '';

        foreach ($this->store->getStoresStructure(false, $storeIds) as $website) {
            $visibility .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $visibility .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $visibility .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }

        return $visibility;
    }
}
