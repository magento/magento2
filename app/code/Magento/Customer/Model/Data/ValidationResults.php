<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

/**
 * Validation results data model.
 */
class ValidationResults extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Customer\Api\Data\ValidationResultsInterface
{
    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return $this->_get(self::VALID);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->_get(self::MESSAGES);
    }

    /**
     * Set if the provided data is valid.
     *
     * @param bool $isValid
     * @return $this
     */
    public function setIsValid($isValid)
    {
        return $this->setData(self::VALID, $isValid);
    }

    /**
     * Set error messages as array in case of validation failure.
     *
     * @param string[] $messages
     * @return string[]
     */
    public function setMessages(array $messages)
    {
        return $this->setData(self::MESSAGES, $messages);
    }
}
