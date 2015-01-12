<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Address\Renderer;

use Magento\Directory\Model\Country\Format;

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
     * @param \Magento\Framework\Object $type
     * @return void
     */
    public function setType(\Magento\Framework\Object $type);

    /**
     * Retrieve format type object
     *
     * @return \Magento\Framework\Object
     */
    public function getType();

    /**
     * Render address
     *
     * @param \Magento\Customer\Model\Address\AbstractAddress $address
     * @param string|null $format
     * @return mixed
     * @deprecated All new code should use renderArray based on Metadata service
     */
    public function render(\Magento\Customer\Model\Address\AbstractAddress $address, $format = null);

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
