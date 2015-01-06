<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
