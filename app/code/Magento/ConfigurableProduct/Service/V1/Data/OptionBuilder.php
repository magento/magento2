<?php
/**
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
namespace Magento\ConfigurableProduct\Service\V1\Data;

/**
 * @codeCoverageIgnore
 */
class OptionBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Option::ID, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAttributeId($value)
    {
        return $this->_set(Option::ATTRIBUTE_ID, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setLabel($value)
    {
        return $this->_set(Option::LABEL, $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setPosition($value)
    {
        return $this->_set(Option::POSITION, $value);
    }

    /**
     * @param bool $value 
     * @return self 
     */
    public function setType($value)
    {
        return $this->_set(Option::TYPE, $value);
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setUseDefault($value)
    {
        return $this->_set(Option::USE_DEFAULT, $value);
    }

    /**
     * @param \Magento\ConfigurableProduct\Service\V1\Data\Option\Value[] $value
     * @return $this
     */
    public function setValues($value)
    {
        return $this->_set(Option::VALUES, $value);
    }
}
