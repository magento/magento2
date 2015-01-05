<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

/**
 * Catalog product group price backend attribute model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Groupprice extends \Magento\Catalog\Model\Product\Attribute\Backend\Groupprice\AbstractGroupprice
{
    /**
     * Catalog product attribute backend groupprice
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice
     */
    protected $_productAttributeBackendGroupprice;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice $productAttributeBackendGroupprice
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice $productAttributeBackendGroupprice
    ) {
        $this->_productAttributeBackendGroupprice = $productAttributeBackendGroupprice;
        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $catalogProductType,
            $groupManagement
        );
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice
     */
    protected function _getResource()
    {
        return $this->_productAttributeBackendGroupprice;
    }

    /**
     * Error message when duplicates
     *
     * @return string
     */
    protected function _getDuplicateErrorMessage()
    {
        return __('We found a duplicate website group price customer group.');
    }

    /**
     * By default attribute value is considered non-scalar that can be stored in a generic way
     *
     * @return bool
     */
    public function isScalar()
    {
        return false;
    }
}
