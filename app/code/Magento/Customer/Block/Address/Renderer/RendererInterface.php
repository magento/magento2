<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address\Renderer;

use Magento\Directory\Model\Country\Format;
use Magento\Customer\Model\Address\AddressModelInterface;

/**
 * Address renderer interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Set format type object
     *
     * @param \Magento\Framework\DataObject $type
     * @return void
     * @since 2.0.0
     */
    public function setType(\Magento\Framework\DataObject $type);

    /**
     * Retrieve format type object
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getType();

    /**
     * Render address
     *
     * @param AddressModelInterface $address
     * @param string|null $format
     * @return mixed
     * All new code should use renderArray based on Metadata service
     * @since 2.0.0
     */
    public function render(AddressModelInterface $address, $format = null);

    /**
     * Get a format object for a given address attributes, based on the type set earlier.
     *
     * @param null|array $addressAttributes
     * @return Format
     * @since 2.0.0
     */
    public function getFormatArray($addressAttributes = null);

    /**
     * Render address by attribute array
     *
     * @param array $addressAttributes
     * @param Format|null $format
     * @return string
     * @since 2.0.0
     */
    public function renderArray($addressAttributes, $format = null);
}
