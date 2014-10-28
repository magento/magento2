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

namespace Magento\Catalog\Service\V1\Data;

use Magento\Framework\Service\Data\AttributeValueBuilder;

/**
 * @codeCoverageIgnore
 */
class ProductBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param \Magento\Catalog\Service\V1\Product\MetadataServiceInterface $metadataService
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        \Magento\Catalog\Service\V1\Product\MetadataServiceInterface $metadataService
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
    }

    /**
     * Set Sku
     *
     * @param string|null $value
     * @return $this
     */
    public function setSku($value)
    {
        return $this->_set(Product::SKU, $value);
    }

    /**
     * Set Name
     *
     * @param string|null $value
     * @return $this
     */
    public function setName($value)
    {
        return $this->_set(Product::NAME, $value);
    }

    /**
     * Set store id
     *
     * @param int|null $value
     * @return $this
     */
    public function setStoreId($value)
    {
        return $this->_set(Product::STORE_ID, $value);
    }

    /**
     * Set price
     *
     * @param float|null $value
     * @return $this
     */
    public function setPrice($value)
    {
        return $this->_set(Product::PRICE, $value);
    }

    /**
     * Set visibility
     *
     * @param int|null $value
     * @return $this
     */
    public function setVisibility($value)
    {
        return $this->_set(Product::VISIBILITY, $value);
    }

    /**
     * Set TypeId
     *
     * @param int|null $value
     * @return $this
     */
    public function setTypeId($value)
    {
        return $this->_set(Product::TYPE_ID, $value);
    }

    /**
     * Set created time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string|null $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        throw new \Magento\Framework\Exception\InputException(
            'Field "created_at" is readonly',
            ['fieldName' => 'created_at']
        );
    }

    /**
     * Set updated time
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string|null $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        throw new \Magento\Framework\Exception\InputException(
            'Field "updated_at" is readonly',
            ['fieldName' => 'updated_at']
        );
    }

    /**
     * Set status
     *
     * @param int|null $value
     * @return $this
     */
    public function setAttributeSetId($value)
    {
        return $this->_set(Product::ATTRIBUTE_SET_ID, $value);
    }

    /**
     * Set status
     *
     * @param int|null $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->_set(Product::STATUS, $value);
    }

    /**
     * Set weight
     *
     * @param float|null $value
     * @return $this
     */
    public function setWeight($value)
    {
        return $this->_set(Product::WEIGHT, $value);
    }
}
