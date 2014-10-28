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
namespace Magento\CheckoutAgreements\Model;

use Magento\Checkout\Model\Agreements\AgreementsProviderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provide Agreements stored in db
 */
class AgreementsProvider implements AgreementsProviderInterface
{
    /** Path to config node */
    const PATH_ENABLED = 'checkout/options/enable_agreements';

    /** @var \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory */
    protected $agreementCollectionFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var  \Magento\Framework\StoreManagerInterface */
    protected $storeManager;

    /**
     * @param Resource\Agreement\CollectionFactory $agreementCollectionFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->agreementCollectionFactory = $agreementCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get list of required Agreement Ids
     *
     * @return int[]
     */
    public function getRequiredAgreementIds()
    {
        if (!$this->scopeConfig->isSetFlag(self::PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return [];
        } else {
            return $this->agreementCollectionFactory->create()
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->addFieldToFilter('is_active', 1)
                ->getAllIds();
        }
    }
}
