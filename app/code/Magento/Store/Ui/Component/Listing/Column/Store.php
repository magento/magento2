<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class Store
 */
class Store extends Column
{
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $storeKey;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     * @param string $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        array $components = [],
        array $data = [],
        $storeKey = 'store_id'
    ) {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        $this->storeKey = $storeKey;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content = '';
        if (!empty($item[$this->storeKey])) {
            $origStores = $item[$this->storeKey];
        }

        if (empty($origStores)) {
            return '';
        }
        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }
        if (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views');
        }

        $data = $this->systemStore->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $content .= $website['label'] . "<br/>";
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . "<br/>";
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . "<br/>";
                }
            }
        }

        return $content;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->getStoreManager()->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    /**
     * Get StoreManager dependency
     *
     * @return StoreManager
     *
     * @deprecated
     */
    private function getStoreManager()
    {
        if ($this->storeManager === null) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }
}
