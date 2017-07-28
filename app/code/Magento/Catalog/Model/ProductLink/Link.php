<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Link extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductLinkInterface
{
    /**#@+
     * Constants
     */
    const KEY_SKU = 'sku';
    const KEY_LINK_TYPE = 'link_type';
    const KEY_LINKED_PRODUCT_SKU = 'linked_product_sku';
    const KEY_LINKED_PRODUCT_TYPE = 'linked_product_type';
    const KEY_POSITION = 'position';
    /**#@-*/

    /**
     * Retrieves a value from the data array if set, or null otherwise.
     *
     * @param string $key
     * @return mixed|null
     * @since 2.0.0
     */
    protected function _get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Return Data Object data in array format.
     *
     * @return array
     * @todo refactor with converter for AbstractExtensibleModel
     * @since 2.0.0
     */
    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Get SKU
     *
     * @identifier
     * @return string
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->_get(self::KEY_SKU);
    }

    /**
     * Get link type
     *
     * @identifier
     * @return string
     * @since 2.0.0
     */
    public function getLinkType()
    {
        return $this->_get(self::KEY_LINK_TYPE);
    }

    /**
     * Get linked product sku
     *
     * @identifier
     * @return string
     * @since 2.0.0
     */
    public function getLinkedProductSku()
    {
        return $this->_get(self::KEY_LINKED_PRODUCT_SKU);
    }

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     * @since 2.0.0
     */
    public function getLinkedProductType()
    {
        return $this->_get(self::KEY_LINKED_PRODUCT_TYPE);
    }

    /**
     * Get product position
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->_get(self::KEY_POSITION);
    }

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set link type
     *
     * @param string $linkType
     * @return $this
     * @since 2.0.0
     */
    public function setLinkType($linkType)
    {
        return $this->setData(self::KEY_LINK_TYPE, $linkType);
    }

    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     * @return $this
     * @since 2.0.0
     */
    public function setLinkedProductSku($linkedProductSku)
    {
        return $this->setData(self::KEY_LINKED_PRODUCT_SKU, $linkedProductSku);
    }

    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     * @return $this
     * @since 2.0.0
     */
    public function setLinkedProductType($linkedProductType)
    {
        return $this->setData(self::KEY_LINKED_PRODUCT_TYPE, $linkedProductType);
    }

    /**
     * Set linked item position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        if (!$this->_getExtensionAttributes()) {
            $this->setExtensionAttributes(
                $this->extensionAttributesFactory->create(\Magento\Catalog\Model\ProductLink\Link::class)
            );
        }
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
