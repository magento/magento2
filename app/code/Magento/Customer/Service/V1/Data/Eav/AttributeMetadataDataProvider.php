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

namespace Magento\Customer\Service\V1\Data\Eav;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Attribute Metadata data provider class
 *
 * @package Magento\Customer\Service\V1\Data\Eav
 */
class AttributeMetadataDataProvider
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory
     */
    private $attrFormCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * Initialize data provider with data source
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory $attrFormCollectionFactory
     * @param \Magento\Store\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory $attrFormCollectionFactory,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->eavConfig = $eavConfig;
        $this->attrFormCollectionFactory = $attrFormCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get attribute model for a given entity type and code
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return false|AbstractAttribute
     */
    public function getAttribute($entityType, $attributeCode)
    {
        return $this->eavConfig->getAttribute($entityType, $attributeCode);
    }

    /**
     * Get all attribute codes for a given entity type and attribute set
     *
     * @param string $entityType
     * @param int $attributeSetId
     * @param string|null $storeId
     * @return array Attribute codes
     */
    public function getAllAttributeCodes($entityType, $attributeSetId = 0, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $object = new \Magento\Framework\Object(
            [
                'store_id' => $storeId,
                'attribute_set_id' => $attributeSetId,
            ]
        );
        return $this->eavConfig->getEntityAttributeCodes($entityType, $object);
    }

    /**
     * Load collection with filters applied
     *
     * @param string $entityType
     * @param string $formCode
     * @return \Magento\Customer\Model\Resource\Form\Attribute\Collection
     */
    public function loadAttributesCollection($entityType, $formCode)
    {
        $attributesFormCollection = $this->attrFormCollectionFactory->create();
        $attributesFormCollection->setStore($this->storeManager->getStore())
            ->setEntityType($entityType)
            ->addFormCodeFilter($formCode)
            ->setSortOrder();

        return $attributesFormCollection;
    }
}
