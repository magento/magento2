<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model;

/**
 * @codeCoverageIgnore
 */
class CategoryProductLink extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Catalog\Api\Data\CategoryProductLinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->_get('sku');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->_get('position');
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryId()
    {
        return $this->_get('category_id');
    }
}
