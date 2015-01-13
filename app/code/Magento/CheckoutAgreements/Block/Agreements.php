<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Block;

class Agreements extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory
     */
    protected $_agreementCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory $agreementCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory $agreementCollectionFactory,
        array $data = []
    ) {
        $this->_agreementCollectionFactory = $agreementCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getAgreements()
    {
        if (!$this->hasAgreements()) {
            if (!$this->_scopeConfig->isSetFlag('checkout/options/enable_agreements', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $agreements = [];
            } else {
                /** @var \Magento\CheckoutAgreements\Model\Resource\Agreement\Collection $agreements */
                $agreements = $this->_agreementCollectionFactory->create()->addStoreFilter(
                    $this->_storeManager->getStore()->getId()
                )->addFieldToFilter(
                    'is_active',
                    1
                );
            }
            $this->setAgreements($agreements);
        }
        return $this->getData('agreements');
    }
}
