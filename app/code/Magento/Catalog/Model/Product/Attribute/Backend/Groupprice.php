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
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice $productAttributeBackendGroupprice
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Groupprice $productAttributeBackendGroupprice
    ) {
        $this->_productAttributeBackendGroupprice = $productAttributeBackendGroupprice;
        parent::__construct($logger, $currencyFactory, $storeManager, $catalogData, $config, $catalogProductType);
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
}
