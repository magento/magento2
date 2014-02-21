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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Product Flat Helper
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Helper\Product;

class Flat extends \Magento\Catalog\Helper\Flat\AbstractFlat
{
    /**
     * Catalog Product Flat Config
     */
    const XML_PATH_USE_PRODUCT_FLAT          = 'catalog/frontend/flat_catalog_product';

    /**
     * @var int
     */
    protected $_addFilterableAttrs;

    /**
     * @var int
     */
    protected $_addChildData;

    /**
     * Catalog Flat Product index process code
     */
    const CATALOG_FLAT_PROCESS_CODE = 'catalog_product_flat';

    /**
     * Catalog Product Flat index process code
     *
     * @var string
     */
    protected $_indexerCode = self::CATALOG_FLAT_PROCESS_CODE;

    /**
     * Catalog Product Flat index process instance
     *
     * @var \Magento\Index\Model\Process|null
     */
    protected $_process = null;

    /**
     * Store flags which defines if Catalog Product Flat functionality is enabled
     *
     * @deprecated after 1.7.0.0
     *
     * @var array
     */
    protected $_isEnabled = array();

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Index\Model\ProcessFactory $processFactory
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param mixed $addFilterableAttrs
     * @param mixed $addChildData
     * @param bool $isAvailable
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Index\Model\ProcessFactory $processFactory,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        $addFilterableAttrs = 0,
        $addChildData = 0,
        $isAvailable = true
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context, $processFactory, $isAvailable);
        $this->_addFilterableAttrs = intval($addFilterableAttrs);
        $this->_addChildData = intval($addChildData);
    }

    /**
     * Check Catalog Product Flat functionality is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_USE_PRODUCT_FLAT);
    }

    /**
     * Is add filterable attributes to Flat table
     *
     * @return int
     */
    public function isAddFilterableAttributes()
    {
        return $this->_addFilterableAttrs;
    }

    /**
     * Is add child data to Flat
     *
     * @return int
     */
    public function isAddChildData()
    {
        return $this->_addChildData;
    }
}
