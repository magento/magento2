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
 */
interface RendererInterface
{
    /**
     * Set format type object
     *
     * @param \Magento\Framework\DataObject $type
     * @return void
     */
    public function setType(\Magento\Framework\DataObject $type);

    /**
     * Retrieve format type object
     *
     * @return \Magento\Framework\DataObject
     */
    public function getType();

    /**
     * Render address
     *
     * @param AddressModelInterface $address
     * @param string|null $format
     * @return mixed
     * All new code should use renderArray based on Metadata service
     */
    public function render(AddressModelInterface $address, $format = null);

    /**
     * Get a format object for a given address attributes, based on the type set earlier.
     *
     * @param null|array $addressAttributes
     * @return Format
     */
    public function getFormatArray($addressAttributes = null);

    /**
     * Render address by attribute array
     *
     * @param array $addressAttributes
     * @param Format|null $format
     * @return string
     */
    public function renderArray($addressAttributes, $format = null);
}
