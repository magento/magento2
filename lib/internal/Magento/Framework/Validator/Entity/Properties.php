<?php
/**
 * Validates properties of entity (\Magento\Framework\DataObject).
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Entity;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;

class Properties extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var string[]
     */
    protected $_readOnlyProperties = [];

    /**
     * Set read-only properties.
     *
     * @param string[] $readOnlyProperties
     * @return void
     */
    public function setReadOnlyProperties(array $readOnlyProperties)
    {
        $this->_readOnlyProperties = $readOnlyProperties;
    }

    /**
     * Successful if $value is \Magento\Framework\Model\AbstractModel an all condition are fulfilled.
     *
     * If read-only properties are set than $value mustn't have changes in them.
     *
     * @param AbstractModel $value
     * @return bool
     * @throws \InvalidArgumentException when $value is not instanceof \Magento\Framework\DataObject
     * @api
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!$value instanceof AbstractModel) {
            throw new \InvalidArgumentException('Instance of \Magento\Framework\Model\AbstractModel is expected.');
        }
        if ($this->_readOnlyProperties) {
            if (!$value->hasDataChanges()) {
                return true;
            }
            foreach ($this->_readOnlyProperties as $property) {
                if ($this->_hasChanges($value->getData($property), $value->getOrigData($property))) {
                    $this->_messages[__CLASS__] = [
                        (string)new \Magento\Framework\Phrase("Read-only property cannot be changed.")
                    ];
                    break;
                }
            }
        }
        return !count($this->_messages);
    }

    /**
     * Compare two values as numbers and as other types
     *
     * @param mixed $firstValue
     * @param mixed $secondValue
     * @return bool
     */
    protected function _hasChanges($firstValue, $secondValue)
    {
        if ($firstValue === $secondValue || $firstValue == $secondValue && is_numeric(
            $firstValue
        ) && is_numeric(
            $secondValue
        )
        ) {
            return false;
        }
        return true;
    }
}
