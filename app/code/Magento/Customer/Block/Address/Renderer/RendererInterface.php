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
