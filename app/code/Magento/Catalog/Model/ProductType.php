<?php
/**
 * Product type
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
