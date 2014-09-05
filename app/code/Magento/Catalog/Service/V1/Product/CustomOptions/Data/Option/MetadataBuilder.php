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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option;

use Magento\Framework\Service\Data\AttributeValueBuilder;

/**
 * @codeCoverageIgnore
 */
class MetadataBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * @var string[]
     */
    protected $customAttributeCodes = [
        Metadata::SORT_ORDER,
        Metadata::TITLE,
        Metadata::FILE_EXTENSION,
        Metadata::IMAGE_SIZE_X,
        Metadata::IMAGE_SIZE_Y,
        Metadata::MAX_CHARACTERS,
        Metadata::OPTION_TYPE_ID
    ];

    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param \Magento\Framework\Service\Config\MetadataConfig $metadataService
     * @param array $customAttributeCodes
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        \Magento\Framework\Service\Config\MetadataConfig $metadataService,
        array $customAttributeCodes = array()
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->customAttributeCodes = array_merge($this->customAttributeCodes, $customAttributeCodes);
    }

    /**
     * Set price
     *
     * @param float $value
     * @return $this
     */
    public function setPrice($value)
    {
        return $this->_set(Metadata::PRICE, $value);
    }

    /**
     * Set price type
     *
     * @param string $value
     * @return $this
     */
    public function setPriceType($value)
    {
        return $this->_set(Metadata::PRICE_TYPE, $value);
    }

    /**
     * Set Sku
     *
     * @param string $value
     * @return $this
     */
    public function setSku($value)
    {
        return $this->_set(Metadata::SKU, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesCodes()
    {
        return array_merge($this->customAttributeCodes, parent::getCustomAttributesCodes());
    }
}
