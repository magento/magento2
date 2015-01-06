<?php
/**
 * Product type
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductTypeInterface;

/**
 * @codeCoverageIgnore
 */
class ProductType extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get('name');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->_get('label');
    }
}
