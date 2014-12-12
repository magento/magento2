<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\AbstractExtensibleObject;

class Simple extends AbstractExtensibleObject
{
    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->_get('entityId');
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->_get('name');
    }
}
