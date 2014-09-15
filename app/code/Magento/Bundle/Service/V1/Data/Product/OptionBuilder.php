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
namespace Magento\Bundle\Service\V1\Data\Product;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class OptionBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Set option id
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Option::ID, $value);
    }

    /**
     * Set option title
     *
     * @param string $value
     * @return $this
     */
    public function setTitle($value)
    {
        return $this->_set(Option::TITLE, $value);
    }

    /**
     * Set is required option
     *
     * @param bool $value
     * @return $this
     */
    public function setRequired($value)
    {
        return $this->_set(Option::REQUIRED, $value);
    }

    /**
     * Set input type
     *
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        return $this->_set(Option::TYPE, $value);
    }

    /**
     * Set option position
     *
     * @param int $value
     * @return $this
     */
    public function setPosition($value)
    {
        return $this->_set(Option::POSITION, $value);
    }

    /**
     * Set product sku
     *
     * @param string $value
     * @return $this
     */
    public function setSku($value)
    {
        return $this->_set(Option::SKU, $value);
    }

    /**
     * Set product links
     *
     * @param \Magento\Bundle\Service\V1\Data\Product\Link[] $value
     * @return $this
     */
    public function setProductLinks($value)
    {
        return $this->_set(Option::PRODUCT_LINKS, $value);
    }
}
