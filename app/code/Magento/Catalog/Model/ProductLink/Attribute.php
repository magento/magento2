<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductLinkAttributeInterface;

/**
 * @codeCoverageIgnore
 */
class Attribute extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductLinkAttributeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_get('code');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_get('type');
    }
}
