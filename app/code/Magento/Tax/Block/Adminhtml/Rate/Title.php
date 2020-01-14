<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Block\Adminhtml\Rate;

use Magento\Tax\Controller\RegistryConstants;

/**
 * Tax Rate Titles Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Title extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $_titles;

    /**
     * @var string
     */
    protected $_template = 'Magento_Tax::rate/title.phtml';

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_taxRateRepository = $taxRateRepository;
        $this->_storeFactory = $storeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return the tax rate titles associated with a store view.
     *
     * @return array
     */
    public function getTitles()
    {
        if ($this->_titles === null) {
            $this->_titles = [];

            $taxRateId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_TAX_RATE_ID);
            $titles = [];
            if ($taxRateId) {
                $rate = $this->_taxRateRepository->get($taxRateId);
                $titles = $rate->getTitles();
            }

            foreach ($titles as $title) {
                $this->_titles[$title->getStoreId()] = $title->getValue();
            }
            foreach ($this->getStores() as $store) {
                if (!isset($this->_titles[$store->getId()])) {
                    $this->_titles[$store->getId()] = '';
                }
            }
        }
        return $this->_titles;
    }

    /**
     * @return mixed
     */
    public function getStores()
    {
        $stores = $this->getData('stores');
        if ($stores === null) {
            $stores = $this->_storeFactory->create()->getResourceCollection()->setLoadDefault(false)->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
}
