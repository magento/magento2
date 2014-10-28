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
namespace Magento\Catalog\Model\Entity;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;

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
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    /**
     * Event Prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_entity_attribute';

    /**
     * Event Object
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    const MODULE_NAME = 'Magento_Catalog';

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param LockValidatorInterface $lockValidator
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        LockValidatorInterface $lockValidator,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->attrLockValidator = $lockValidator;
        parent::__construct(
            $context,
            $registry,
            $coreData,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Processing object before save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Eav\Exception
     */
    protected function _beforeSave()
    {
        try {
            $this->attrLockValidator->validate($this);
        } catch (\Magento\Framework\Model\Exception $exception) {
            throw new \Magento\Eav\Exception($exception->getMessage());
        }

        $this->setData('modulePrefix', self::MODULE_NAME);
        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Framework\Model\AbstractModel
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
