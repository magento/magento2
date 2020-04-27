<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Interface returned in case of incorrect price passed to efficient price API.
 * @api
 * @since 102.0.0
 */
interface PriceUpdateResultInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const MESSAGE = 'message';
    const PARAMETERS = 'parameters';
    /**#@-*/

    /**
     * Get error message, that contains description of error occurred during price update.
     *
     * @return string
     * @since 102.0.0
     */
    public function getMessage();

    /**
     * Set error message, that contains description of error occurred during price update.
     *
     * @param string $message
     * @return $this
     * @since 102.0.0
     */
    public function setMessage($message);

    /**
     * Get parameters, that could be displayed in error message placeholders.
     *
     * @return string[]
     * @since 102.0.0
     */
    public function getParameters();

    /**
     * Set parameters, that could be displayed in error message placeholders.
     *
     * @param string[] $parameters
     * @return $this
     * @since 102.0.0
     */
    public function setParameters(array $parameters);

    /**
     * Retrieve existing extension attributes object.
     *
     * If extension attributes do not exist return null.
     *
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface|null
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface $extensionAttributes
    );
}
