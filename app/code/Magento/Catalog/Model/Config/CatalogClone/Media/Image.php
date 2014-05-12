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
namespace Magento\Catalog\Model\Config\CatalogClone\Media;

/**
 * Clone model for media images related config fields
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Image extends \Magento\Framework\App\Config\Value
{
    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Get fields prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        // use cached eav config
        $entityTypeId = $this->_eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();

        /* @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->_attributeCollectionFactory->create();
        $collection->setEntityTypeFilter($entityTypeId);
        $collection->setFrontendInputTypeFilter('media_image');

        $prefixes = array();

        foreach ($collection as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $prefixes[] = array(
                'field' => $attribute->getAttributeCode() . '_',
                'label' => $attribute->getFrontend()->getLabel()
            );
        }

        return $prefixes;
    }
}
