<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Do not use this class. It was kept for backward compatibility.
 *
 * @deprecated \Magento\GraphQl\Model\Query\Context is used instead of this
 */
class Context extends \Magento\Framework\Model\AbstractExtensibleModel implements ContextInterface
{
    /**#@+
     * Constants defined for type of context
     */
    const USER_TYPE_ID  = 'user_type';
    const USER_ID = 'user_id';
    /**#@-*/

    /**
     * Get extension attributes
     *
     * @return \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface
     */
    public function getExtensionAttributes() : \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set extension attributes
     *
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes
     * @return ContextInterface
     */
    public function setExtensionAttributes(
        \Magento\Framework\GraphQl\Query\Resolver\ContextExtensionInterface $extensionAttributes
    ) : ContextInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId() : int
    {
        return (int) $this->getData(self::USER_ID);
    }

    /**
     * Set user id
     *
     * @param int $userId
     * @return ContextInterface
     */
    public function setUserId(int $userId) : ContextInterface
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Get user type
     *
     * @return int
     */
    public function getUserType() : int
    {
        return (int) $this->getData(self::USER_TYPE_ID);
    }

    /**
     * Set user type
     *
     * @param int $typeId
     * @return ContextInterface
     */
    public function setUserType(int $typeId) : ContextInterface
    {
        return $this->setData(self::USER_TYPE_ID, $typeId);
    }
}
