<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
                $agreements = array();
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
