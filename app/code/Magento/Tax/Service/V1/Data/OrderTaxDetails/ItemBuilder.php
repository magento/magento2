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
namespace Magento\Tax\Service\V1\Data\OrderTaxDetails;

use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Builder for the Item Data Object
 *
 * @method Item create()
 */

class ItemBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Applied Tax data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTaxBuilder
     */
    protected $appliedTaxBuilder;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param AppliedTaxBuilder $appliedTaxBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        AppliedTaxBuilder $appliedTaxBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->appliedTaxBuilder = $appliedTaxBuilder;
    }

    /**
     * Set type (shipping, product, weee, gift wrapping, etc.)
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_set(Item::KEY_TYPE, $type);
        return $this;
    }

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->_set(Item::KEY_ITEM_ID, $itemId);
        return $this;
    }

    /**
     * Set associated item id
     *
     * @param int $associatedItemId
     * @return $this
     */
    public function setAssociatedItemId($associatedItemId)
    {
        $this->_set(Item::KEY_ASSOCIATED_ITEM_ID, $associatedItemId);
        return $this;
    }

    /**
     * Set applied taxes for the item
     *
     * @param \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax[] $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes($appliedTaxes)
    {
        $this->_set(Item::KEY_APPLIED_TAXES, $appliedTaxes);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (isset($data[Item::KEY_APPLIED_TAXES])) {
            $appliedTaxDataObjects = [];
            $appliedTaxes = $data[Item::KEY_APPLIED_TAXES];
            foreach ($appliedTaxes as $appliedTax) {
                $appliedTaxDataObjects[] = $this->appliedTaxBuilder->populateWithArray($appliedTax)->create();
            }
            $data[Item::KEY_APPLIED_TAXES] = $appliedTaxDataObjects;
        }

        return parent::_setDataValues($data);
    }
}
