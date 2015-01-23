<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Api\Data;

interface AgreementInterface
{
    /**
     * Returns the agreement ID.
     *
     * @return int Agreement ID.
     */
    public function getAgreementId();

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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * Returns the agreement content type.
     *
     * @return bool * true - HTML.
     * * false - plain text.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsHtml();
}
