<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Service\Entity;

class SimpleBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->data['entityId'] = $entityId;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
    }
}
