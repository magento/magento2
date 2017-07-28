<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @api
 * @since 2.0.0
 */
class Websites extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'websites';

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @deprecated 2.1.0
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->storeManager->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
