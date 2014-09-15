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
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

use \Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory as AgreementCollectionFactory;
use \Magento\CheckoutAgreements\Model\Resource\Agreement\Collection as AgreementCollection;
use \Magento\CheckoutAgreements\Model\Agreement;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\StoreManagerInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\CheckoutAgreements\Service\V1\Data\AgreementBuilder;
use \Magento\CheckoutAgreements\Service\V1\Data\Agreement as AgreementDataObject;

class ReadService implements ReadServiceInterface
{
    /**
     * @var AgreementCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AgreementBuilder
     */
    private $agreementBuilder;

    /**
     * @var  \Magento\Framework\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param AgreementCollectionFactory $collectionFactory
     * @param AgreementBuilder $agreementBuilder
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AgreementCollectionFactory $collectionFactory,
        AgreementBuilder $agreementBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->agreementBuilder = $agreementBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        if (!$this->scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE)) {
            return array();
        }
        $storeId = $this->storeManager->getStore()->getId();
        /** @var $agreementCollection AgreementCollection */
        $agreementCollection = $this->collectionFactory->create();
        $agreementCollection->addStoreFilter($storeId);
        $agreementCollection->addFieldToFilter('is_active', 1);

        $agreementDataObjects = array();
        foreach ($agreementCollection as $agreement) {
            $agreementDataObjects[] = $this->createAgreementDataObject($agreement);
        }

        return $agreementDataObjects;
    }

    /**
     * Create agreement data object based on given agreement model
     *
     * @param Agreement $agreement
     * @return AgreementDataObject
     */
    protected function createAgreementDataObject(Agreement $agreement)
    {
        $this->agreementBuilder->populateWithArray(array(
            AgreementDataObject::ID => $agreement->getId(),
            AgreementDataObject::NAME => $agreement->getName(),
            AgreementDataObject::CONTENT => $agreement->getContent(),
            AgreementDataObject::CONTENT_HEIGHT => $agreement->getContentHeight(),
            AgreementDataObject::CHECKBOX_TEXT => $agreement->getCheckboxText(),
            AgreementDataObject::ACTIVE => (bool)$agreement->getIsActive(),
            AgreementDataObject::HTML => (bool)$agreement->getIsHtml(),
        ));
        return $this->agreementBuilder->create();
    }
}
