<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CheckoutAgreements\Api\Data;

/**
 * @see \Magento\CheckoutAgreements\Service\V1\Data\Agreement
 */
interface AgreementInterface
{
    /**
     * Returns the agreement ID.
     *
     * @return int Agreement ID.
     */
    public function getId();

    /**
     * Returns the agreement name.
     *
     * @return string Agreement name.
     */
    public function getName();

    /**
     * Returns the agreement content.
     *
     * @return string Agreement content.
     */
    public function getContent();

    /**
     * Returns the agreement content height, which is an optional CSS property.
     *
     * @return string|null Agreement content height. Otherwise, null.
     */
    public function getContentHeight();

    /**
     * Returns the agreement checkbox text.
     *
     * @return string Agreement checkbox text.
     */
    public function getCheckboxText();

    /**
     * Returns the agreement status.
     *
     * @return bool Agreement status.
     */
    public function getIsActive();

    /**
     * Returns the agreement content type.
     *
     * @return bool * true - HTML.
     * * false - plain text.
     */
    public function getIsHtml();
}
