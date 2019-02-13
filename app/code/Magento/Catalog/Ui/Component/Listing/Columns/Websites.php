<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Helper;

/**
 * @api
 * @since 100.0.2
 */
class Websites extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'websites';

    /**
     * Data for concatenated website names value
     */
    const WEBSITE_NAMES = 'website_names';

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     * @param Helper $resourceHelper
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        Helper $resourceHelper = null
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper ?: $objectManager->get(Helper::class);
    }

    /**
     * {@inheritdoc}
     * @deprecated 101.0.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $websiteNames = [];
        foreach ($this->getData('options') as $website) {
            $websiteNames[$website->getWebsiteId()] = $website->getName();
        }
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $websites = [];
                foreach ($item[$fieldName] as $websiteId) {
                    if (!isset($websiteNames[$websiteId])) {
                        continue;
                    }
                    $websites[] = $websiteNames[$websiteId];
                }
                $item[$fieldName] = implode(', ', $websites);
            }
        }

        return $dataSource;
    }
    
    /**
     * Prepare component configuration
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->storeManager->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    /**
     * Apply sorting
     *
     * @return void
     */
    protected function applySorting()
    {
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $collection = $this->getContext()->getDataProvider()->getCollection();
            $collection
                 ->joinField(
                     'websites_ids',
                     'catalog_product_website',
                     'website_id',
                     'product_id=entity_id',
                     null,
                     'left'
                 )
                 ->joinTable(
                     'store_website',
                     'website_id = websites_ids',
                     ['name'],
                     null,
                     'left'
                 )
                 ->groupByAttribute('entity_id');
            $this->_resourceHelper->addGroupConcatColumn(
                $collection->getSelect(),
                self::WEBSITE_NAMES,
                'name'
            );

            $collection->getSelect()->order(self::WEBSITE_NAMES . ' ' . $sorting['direction']);
        }
    }
}
