<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule5\Service\V1\Entity;

use Magento\Framework\Api\AbstractSimpleObjectBuilder;

/**
 * Some Data Object short description.
 *
 * Data Object long
 * multi line description.
 */
class AllSoapAndRestBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * @param int $id
     * @return AllSoapAndRestBuilder
     */
    public function setEntityId($id)
    {
        return $this->_set(AllSoapAndRest::ID, $id);
    }

    /**
     * @param string $name
     * @return AllSoapAndRestBuilder
     */
    public function setName($name)
    {
        return $this->_set(AllSoapAndRest::NAME, $name);
    }

    /**
     * Set flag if entity is enabled
     *
     * @param bool $isEnabled
     * @return AllSoapAndRestBuilder
     */
    public function setIsEnabled($isEnabled)
    {
        return $this->_set(AllSoapAndRest::ENABLED, $isEnabled);
    }

    /**
     * Set flag if entity has orders
     *
     * @param bool $hasOrders
     * @return AllSoapAndRestBuilder
     */
    public function setHasOrders($hasOrders)
    {
        return $this->_set(AllSoapAndRest::HAS_ORDERS, $hasOrders);
    }
}
