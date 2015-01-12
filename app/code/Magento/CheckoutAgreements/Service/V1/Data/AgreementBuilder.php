<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Service\V1\Data;

use Magento\Framework\Api\ExtensibleObjectBuilder;

/**
 * Checkout agreement data object builder.
 *
 * @codeCoverageIgnore
 */
class AgreementBuilder extends ExtensibleObjectBuilder
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
