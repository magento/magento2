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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data;

/**
 * @codeCoverageIgnore
 */
class OptionBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set option id
     *
     * @param int|null $value
     * @return $this
     */
    public function setOptionId($value)
    {
        return $this->_set(Option::OPTION_ID, $value);
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
     * Set option type
     *
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        return $this->_set(Option::TYPE, $value);
    }

    /**
     * Set sort order
     *
     * @param int $value
     * @return $this
     */
    public function setSortOrder($value)
    {
        return $this->_set(Option::SORT_ORDER, $value);
    }

    /**
     * Set is require
     *
     * @param bool $value
     * @return $this
     */
    public function setIsRequire($value)
    {
        return $this->_set(Option::IS_REQUIRE, $value);
    }

    /**
     * Set option metadata
     *
     * @param \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata[] $value
     * @return $this
     */
    public function setMetadata($value)
    {
        return $this->_set(Option::METADATA, $value);
    }
}
