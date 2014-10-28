<?php
/**
 * Validates properties of entity (\Magento\Framework\Object).
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Validator\Entity;

use Magento\Framework\Object;

class Properties extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var string[]
     */
    protected $_readOnlyProperties = array();

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
     * Successful if $value is \Magento\Framework\Object an all condition are fulfilled.
     *
     * If read-only properties are set than $value mustn't have changes in them.
     *
     * @param Object $value
     * @return bool
     * @throws \InvalidArgumentException when $value is not instanceof \Magento\Framework\Object
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!$value instanceof Object) {
            throw new \InvalidArgumentException('Instance of \Magento\Framework\Object is expected.');
        }
        if ($this->_readOnlyProperties) {
            if (!$value->hasDataChanges()) {
                return true;
            }
            foreach ($this->_readOnlyProperties as $property) {
                if ($this->_hasChanges($value->getData($property), $value->getOrigData($property))) {
                    $this->_messages[__CLASS__] = array(__("Read-only property cannot be changed."));
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
