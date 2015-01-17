<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductLinkTypeInterface;

/**
 * @codeCoverageIgnore
 */
class Type extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductLinkTypeInterface
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
    public function getName()
    {
        return $this->_get('name');
    }
}
