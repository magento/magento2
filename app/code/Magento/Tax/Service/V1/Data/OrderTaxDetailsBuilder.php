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

namespace Magento\Tax\Service\V1\Data;

use \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTaxBuilder;
use \Magento\Tax\Service\V1\Data\OrderTaxDetails\ItemBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Builder for the OrderTaxDetails Data Object
 *
 * @method OrderTaxDetails create()
 */
class OrderTaxDetailsBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Applied Tax data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTaxBuilder
     */
    protected $appliedTaxBuilder;

    /**
     * Order item applied tax  data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\OrderTaxDetails\ItemBuilder
     */
    protected $itemBuilder;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param AppliedTaxBuilder $appliedTaxBuilder
     * @param ItemBuilder $itemBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        AppliedTaxBuilder $appliedTaxBuilder,
        ItemBuilder $itemBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->appliedTaxBuilder = $appliedTaxBuilder;
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * Convenience method that returns AppliedTaxBuilder
     *
     * @return AppliedTaxBuilder
     */
    public function getAppliedTaxBuilder()
    {
        return $this->appliedTaxBuilder;
    }

    /**
     * Convenience method that returns ItemBuilder
     *
     * @return ItemBuilder
     */
    public function getItemBuilder()
    {
        return $this->itemBuilder;
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Service\V1\Data\OrderTaxDetails\AppliedTax[] | null $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes($appliedTaxes)
    {
        $this->_set(OrderTaxDetails::KEY_APPLIED_TAXES, $appliedTaxes);
        return $this;
    }

    /**
     * Set Tax Details items
     *
     * @param \Magento\Tax\Service\V1\Data\OrderTaxDetails\Item[] | null $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->_set(OrderTaxDetails::KEY_ITEMS, $items);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _setDataValues(array $data)
    {
        if (isset($data[OrderTaxDetails::KEY_APPLIED_TAXES])) {
            $appliedTaxDataObjects = [];
            $appliedTaxes = $data[OrderTaxDetails::KEY_APPLIED_TAXES];
            foreach ($appliedTaxes as $appliedTax) {
                $appliedTaxDataObjects[] = $this->appliedTaxBuilder
                    ->populateWithArray($appliedTax)->create();
            }
            $data[OrderTaxDetails::KEY_APPLIED_TAXES] = $appliedTaxDataObjects;
        }

        if (isset($data[OrderTaxDetails::KEY_ITEMS])) {
            $taxDetailItemDataObjects = [];
            $taxDetailItems = $data[OrderTaxDetails::KEY_ITEMS];
            foreach ($taxDetailItems as $taxDetailItem) {
                $taxDetailItemDataObjects[] = $this->itemBuilder
                    ->populateWithArray($taxDetailItem)->create();
            }
            $data[OrderTaxDetails::KEY_ITEMS] = $taxDetailItemDataObjects;
        }

        return parent::_setDataValues($data);
    }
}
