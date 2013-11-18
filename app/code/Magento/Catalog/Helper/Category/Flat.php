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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog flat helper
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Helper\Category;

class Flat extends \Magento\Catalog\Helper\Flat\AbstractFlat
{
    /**
     * Catalog Category Flat Is Enabled Config
     */
    const XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY = 'catalog/frontend/flat_catalog_category';

    /**
     * Catalog Flat Category index process code
     */
    const CATALOG_CATEGORY_FLAT_PROCESS_CODE = 'catalog_category_flat';

    /**
     * Catalog Category Flat index process code
     *
     * @var string
     */
    protected $_indexerCode = self::CATALOG_CATEGORY_FLAT_PROCESS_CODE;

    /**
     * Store catalog Category Flat index process instance
     *
     * @var \Magento\Index\Model\Process|null
     */
    protected $_process = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Catalog category flat
     *
     * @var \Magento\Catalog\Model\Resource\Category\Flat
     */
    protected $_catalogCategoryFlat;

    /**
     * Construct
     *
     * @param \Magento\Index\Model\ProcessFactory $processFactory
     * @param \Magento\Catalog\Model\Resource\Category\Flat $catalogCategoryFlat
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(
        \Magento\Index\Model\ProcessFactory $processFactory,
        \Magento\Catalog\Model\Resource\Category\Flat $catalogCategoryFlat,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        $this->_catalogCategoryFlat = $catalogCategoryFlat;
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($processFactory, $context);
    }

    /**
     * Check if Catalog Category Flat Data is enabled
     *
     * @param bool $skipAdminCheck this parameter is deprecated and no longer in use
     *
     * @return bool
     */
    public function isEnabled($skipAdminCheck = false)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY);
    }

    /**
     * Check if Catalog Category Flat Data has been initialized
     *
     * @return bool
     */
    public function isBuilt()
    {
        return $this->_catalogCategoryFlat->isBuilt();
    }

    /**
     * Check if Catalog Category Flat Data has been initialized
     *
     * @deprecated after 1.7.0.0 use \Magento\Catalog\Helper\Category\Flat::isBuilt() instead
     *
     * @return bool
     */
    public function isRebuilt()
    {
        return $this->isBuilt();
    }
}
