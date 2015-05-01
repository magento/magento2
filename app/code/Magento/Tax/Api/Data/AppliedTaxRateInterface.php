<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

interface AppliedTaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TITLE = 'title';

    const KEY_PERCENT = 'percent';
    /**#@-*/

    /**
     * Get code
     *
     * @api
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     *
     * @api
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get Title
     *
     * @api
     * @return string|null
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @api
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get Tax Percent
     *
     * @api
     * @return float|null
     */
    public function getPercent();

    /**
     * Set Tax Percent
     *
     * @api
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes);
}
