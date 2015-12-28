<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Scope
 */
class Scope extends Column
{
    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SystemStore $systemStore,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->storeManager = $storeManager;
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
        $storeViewIds = null;
        $websiteIds = null;

        if ($item['scope_type'] === 'stores') {
            $storeViewName = $this->storeManager->getStore($item['scope_id'])->getName();
            return __($storeViewName . ' (store view)');
        }
        else if ($item['scope_type'] === 'websites') {
            $websiteName = $this->storeManager->getWebsite($item['scope_id'])->getName();
            return __($websiteName . ' (website)');
        }
        else if ($item['scope_type'] === 'default') {
            return __('Global');
        }

        $data = $this->systemStore->getStoresStructure(false, $storeViewIds, [], $websiteIds);

        foreach ($data as $website) {
            $content .= $website['label'];
            foreach ($website['children'] as $group) {
                $content .= str_repeat(' ', 3) . $group['label'];
                foreach ($group['children'] as $store) {
                    $content .= str_repeat(' ', 6) . $store['label'];
                }
            }
        }

        return $content;
    }
}
