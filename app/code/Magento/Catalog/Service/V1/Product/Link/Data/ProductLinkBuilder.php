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

namespace Magento\Catalog\Service\V1\Product\Link\Data;

use Magento\Framework\Service\Data\AttributeValueBuilder;

/**
 * Builder for the ProductLink Service Data Object
 *
 * @method ProductLink create()
 * @codeCoverageIgnore
 */
class ProductLinkBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param \Magento\Framework\Service\Config\MetadataConfig $metadataService
     * @param array $customAttributesCodes
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        \Magento\Framework\Service\Config\MetadataConfig $metadataService,
        array $customAttributesCodes = array()
    ) {
        $this->customAttributes = $customAttributesCodes;
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
    }
    
    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->_set(ProductLink::TYPE, $type);
    }

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->_set(ProductLink::SKU, $sku);
    }

    /**
     * Set product position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->_set(ProductLink::POSITION, $position);
    }

    /**
     * Get custom attributes codes
     *
     * @return string[]
     */
    public function getCustomAttributesCodes()
    {
        return array_merge(parent::getCustomAttributesCodes(), $this->customAttributes);
    }
}
