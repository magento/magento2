<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\Framework\DataObject;
use Magento\Framework\App\ObjectManager;

/**
 * Customer attribute resource model
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Attribute
{
    /**
     * @var AttributeMetadataCache
     */
    private $attributeMetadataCache;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Type $eavEntityType
     * @param string $connectionName
     * @param \Magento\Customer\Model\Metadata\AttributeMetadataCache $attributeMetadataCache
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Type $eavEntityType,
        $connectionName = null,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        $this->attributeMetadataCache = $attributeMetadataCache ?: ObjectManager::getInstance()
            ->get(AttributeMetadataCache::class);
        parent::__construct($context, $storeManager, $eavEntityType, $connectionName);
    }

    /**
     * Get EAV website table
     *
     * Get table, where website-dependent attribute parameters are stored
     * If realization doesn't demand this functionality, let this function just return null
     *
     * @return string|null
     */
    protected function _getEavWebsiteTable()
    {
        return $this->getTable('customer_eav_attribute_website');
    }

    /**
     * Get Form attribute table
     *
     * Get table, where dependency between form name and attribute ids is stored
     *
     * @return string|null
     */
    protected function _getFormAttributeTable()
    {
        return $this->getTable('customer_form_attribute');
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(DataObject $object)
    {
        $this->attributeMetadataCache->clean();
        parent::afterSave($object);
    }

    /**
     * {@inheritDoc}
     */
    public function afterDelete(DataObject $object)
    {
        $this->attributeMetadataCache->clean();
        parent::afterDelete($object);
    }
}
