<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml catalog product action attribute update helper
 */
namespace Magento\Catalog\Helper\Product\Edit\Action;

/**
 * Class Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute extends \Magento\Backend\Helper\Data
{
    /**
     * Selected products for mass-update
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_products;

    /**
     * Array of same attributes for selected products
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $_attributes;

    /**
     * Excluded from batch update attribute codes
     *
     * @var string[]
     */
    protected $_excludedAttributes = ['url_key'];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Route\Config $routeConfig,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Backend\Model\Session $session,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_session = $session;
        $this->_productsFactory = $productsFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $routeConfig, $locale, $backendUrl, $auth, $frontNameResolver, $mathRandom);
    }

    /**
     * Return product collection with selected product filter
     * Product collection didn't load
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProducts()
    {
        if ($this->_products === null) {
            $productsIds = $this->getProductIds();

            if (!is_array($productsIds)) {
                $productsIds = [0];
            }

            $this->_products = $this->_productsFactory->create()->setStoreId(
                $this->getSelectedStoreId()
            )->addIdFilter(
                $productsIds
            );
        }

        return $this->_products;
    }

    /**
     * Set array of selected product
     *
     * @param array $productIds
     *
     * @return void
     */
    public function setProductIds($productIds)
    {
        $this->_session->setProductIds($productIds);
    }

    /**
     * Return array of selected product ids from post or session
     *
     * @return array|null
     */
    public function getProductIds()
    {
        return $this->_session->getProductIds();
    }

    /**
     * Return selected store id from request
     *
     * @return integer
     */
    public function getSelectedStoreId()
    {
        return (int)$this->_getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
    }

    /**
     * Return array of attribute sets by selected products
     *
     * @return array
     */
    public function getProductsSetIds()
    {
        return $this->getProducts()->getSetIds();
    }

    /**
     * Return collection of same attributes for selected products without unique
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getAttributeCollection()->addIsNotUniqueFilter()->setInAllAttributeSetsFilter(
                $this->getProductsSetIds()
            );

            if ($this->_excludedAttributes) {
                $this->_attributes->addFieldToFilter('attribute_code', ['nin' => $this->_excludedAttributes]);
            }

            // check product type apply to limitation and remove attributes that impossible to change in mass-update
            $productTypeIds = $this->getProducts()->getProductTypeIds();
            foreach ($this->_attributes as $attribute) {
                /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */
                foreach ($productTypeIds as $productTypeId) {
                    $applyTo = $attribute->getApplyTo();
                    if (count($applyTo) > 0 && !in_array($productTypeId, $applyTo)) {
                        $this->_attributes->removeItemByKey($attribute->getId());
                        break;
                    }
                }
            }
        }

        return $this->_attributes;
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getStoreWebsiteId($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsiteId();
    }
}
