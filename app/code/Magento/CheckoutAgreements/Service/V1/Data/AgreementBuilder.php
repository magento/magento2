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
namespace Magento\CheckoutAgreements\Service\V1\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * Checkout agreement data object builder.
 * 
 * @codeCoverageIgnore
 */
class AgreementBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * Sets the agreement ID.
     *
     * @param int $value The agreement ID.
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Agreement::ID, $value);
    }

    /**
     * Sets the agreement name.
     *
     * @param string $value The agreement name.
     * @return $this
     */
    public function setName($value)
    {
        return $this->_set(Agreement::NAME, $value);
    }

    /**
     * Sets the agreement content.
     *
     * @param string $value The agreement content.
     * @return $this
     */
    public function setContent($value)
    {
        return $this->_set(Agreement::CONTENT, $value);
    }

    /**
     * Sets the agreement content height, which is an optional CSS property.
     *
     * @param string $value The agreement content height.
     * @return $this
     */
    public function setContentHeight($value)
    {
        return $this->_set(Agreement::CONTENT_HEIGHT, $value);
    }

    /**
     * Sets the agreement checkbox text.
     *
     * @param string $value The agreement checkbox text.
     * @return $this
     */
    public function setCheckboxText($value)
    {
        return $this->_set(Agreement::CHECKBOX_TEXT, $value);
    }

    /**
     * Sets the agreement status.
     *
     * @param bool $value The agreement status value. Set to true for active.
     * @return $this
     */
    public function setActive($value)
    {
        return $this->_set(Agreement::ACTIVE, $value);
    }

    /**
     * Sets the agreement content type.
     *
     * @param bool $value The agreement content type. Set to true for HTML. Set to false for plain text.
     * @return $this
     */
    public function setHtml($value)
    {
        return $this->_set(Agreement::HTML, $value);
    }
}
