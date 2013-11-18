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
 * Product attribute extension with event dispatching
 *
 * @method \Magento\Catalog\Model\Resource\Attribute _getResource()
 * @method \Magento\Catalog\Model\Resource\Attribute getResource()
 * @method string getFrontendInputRenderer()
 * @method \Magento\Catalog\Model\Entity\Attribute setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method int getIsVisible()
 * @method int setIsVisible(int $value)
 * @method int getIsSearchable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsSearchable(int $value)
 * @method int getSearchWeight()
 * @method \Magento\Catalog\Model\Entity\Attribute setSearchWeight(int $value)
 * @method int getIsFilterable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsFilterable(int $value)
 * @method int getIsComparable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsComparable(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setIsVisibleOnFront(int $value)
 * @method int getIsHtmlAllowedOnFront()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsHtmlAllowedOnFront(int $value)
 * @method int getIsUsedForPriceRules()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsUsedForPriceRules(int $value)
 * @method int getIsFilterableInSearch()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsFilterableInSearch(int $value)
 * @method int getUsedInProductListing()
 * @method \Magento\Catalog\Model\Entity\Attribute setUsedInProductListing(int $value)
 * @method int getUsedForSortBy()
 * @method \Magento\Catalog\Model\Entity\Attribute setUsedForSortBy(int $value)
 * @method int getIsConfigurable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsConfigurable(int $value)
 * @method string getApplyTo()
 * @method \Magento\Catalog\Model\Entity\Attribute setApplyTo(string $value)
 * @method int getIsVisibleInAdvancedSearch()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsVisibleInAdvancedSearch(int $value)
 * @method int getPosition()
 * @method \Magento\Catalog\Model\Entity\Attribute setPosition(int $value)
 * @method int getIsWysiwygEnabled()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsWysiwygEnabled(int $value)
 * @method int getIsUsedForPromoRules()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsUsedForPromoRules(int $value)
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Entity;

class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    protected $_eventPrefix = 'catalog_entity_attribute';
    protected $_eventObject = 'attribute';
    const MODULE_NAME = 'Magento_Catalog';

    /**
     * Class constructor
     *
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $coreData,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $locale,
            $catalogProductFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Processing object before save data
     *
     * @return \Magento\Core\Model\AbstractModel
     * @throws \Magento\Eav\Exception
     */
    protected function _beforeSave()
    {
        if ($this->_getResource()->isUsedBySuperProducts($this)) {
            throw new \Magento\Eav\Exception(__('This attribute is used in configurable products'));
        }
        $this->setData('modulePrefix', self::MODULE_NAME);
        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        $this->_eavConfig->clear();
        return parent::_afterSave();
    }
}
