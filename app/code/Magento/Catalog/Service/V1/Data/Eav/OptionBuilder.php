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
namespace Magento\Catalog\Service\V1\Data\Eav;

/**
 * Class OptionBuilder
 *
 * @codeCoverageIgnore
 */
class OptionBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set option label
     *
     * @param  string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->_set(Option::LABEL, $label);
    }

    /**
     * Set option value
     *
     * @param  string $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->_set(Option::VALUE, $value);
    }

    /**
     * Get option label
     *
     * @param int $value
     * @return $this
     */
    public function setOrder($value)
    {
        return $this->_set(Option::ORDER, $value);
    }

    /**
     * Get option order
     *
     * @param bool $value
     * @return $this
     */
    public function setDefault($value)
    {
        return $this->_set(Option::IS_DEFAULT, $value);
    }

    /**
     * Set option label for store scope
     *
     * @param  \Magento\Catalog\Service\V1\Data\Eav\Option\Label[] $value
     * @return $this
     */
    public function setStoreLabels($value)
    {
        return $this->_set(Option::STORE_LABELS, $value);
    }
}
