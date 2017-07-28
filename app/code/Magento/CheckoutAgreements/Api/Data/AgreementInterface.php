<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Api\Data;

/**
 * Interface AgreementInterface
 * @api
 * @since 2.0.0
 */
interface AgreementInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const AGREEMENT_ID = 'agreement_id';
    const NAME = 'name';
    const CONTENT = 'content';
    const CONTENT_HEIGHT = 'content_height';
    const CHECKBOX_TEXT = 'checkbox_text';
    const IS_ACTIVE = 'is_active';
    const IS_HTML = 'is_html';
    const MODE = 'mode';
    /**#@-*/

    /**
     * Returns the agreement ID.
     *
     * @return int Agreement ID.
     * @since 2.0.0
     */
    public function getAgreementId();

    /**
     * Sets the agreement ID.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setAgreementId($id);

    /**
     * Returns the agreement name.
     *
     * @return string Agreement name.
     * @since 2.0.0
     */
    public function getName();

    /**
     * Sets the agreement name.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Returns the agreement content.
     *
     * @return string Agreement content.
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Sets the agreement content.
     *
     * @param string $content
     * @return $this
     * @since 2.0.0
     */
    public function setContent($content);

    /**
     * Returns the agreement content height, which is an optional CSS property.
     *
     * @return string|null Agreement content height. Otherwise, null.
     * @since 2.0.0
     */
    public function getContentHeight();

    /**
     * Sets the agreement content height, which is an optional CSS property.
     *
     * @param string|null $height
     * @return $this
     * @since 2.0.0
     */
    public function setContentHeight($height);

    /**
     * Returns the agreement checkbox text.
     *
     * @return string Agreement checkbox text.
     * @since 2.0.0
     */
    public function getCheckboxText();

    /**
     * Sets the agreement checkbox text.
     *
     * @param string $text
     * @return $this
     * @since 2.0.0
     */
    public function setCheckboxText($text);

    /**
     * Returns the agreement status.
     *
     * @return bool Agreement status.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsActive();

    /**
     * Sets the agreement status.
     *
     * @param bool $status
     * @return $this
     * @since 2.0.0
     */
    public function setIsActive($status);

    /**
     * Returns the agreement content type.
     *
     * @return bool * true - HTML.
     * * false - plain text.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsHtml();

    /**
     * Sets the agreement content type.
     * * true - HTML
     * * false - plain text
     *
     * @param bool $isHtml
     * @return $this
     * @since 2.0.0
     */
    public function setIsHtml($isHtml);

    /**
     * Returns the agreement applied mode.
     *
     * @return int
     * @since 2.0.0
     */
    public function getMode();

    /**
     * Sets the agreement applied mode.
     *
     * @param int $mode
     * @return $this
     * @since 2.0.0
     */
    public function setMode($mode);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\CheckoutAgreements\Api\Data\AgreementExtensionInterface $extensionAttributes
    );
}
