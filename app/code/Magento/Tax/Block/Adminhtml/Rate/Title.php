<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Block\Adminhtml\Rate;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Controller\RegistryConstants;

/**
 * Tax Rate Titles Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Title extends Template
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
     * @var StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param StoreFactory $storeFactory
     * @param Registry $coreRegistry
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreFactory $storeFactory,
        Registry $coreRegistry,
        TaxRateRepositoryInterface $taxRateRepository,
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
