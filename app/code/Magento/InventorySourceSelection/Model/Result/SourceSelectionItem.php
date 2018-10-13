<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Result;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemExtensionInterface;

/**
 * @inheritdoc
 */
class SourceSelectionItem extends AbstractExtensibleModel implements SourceSelectionItemInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $qtyToDeduct;

    /**
     * @var float
     */
    private $qtyAvailable;

    /**
     * @param string $sourceCode
     * @param string $sku
     * @param float $qtyToDeduct
     * @param float $qtyAvailable
     */
    public function __construct(string $sourceCode, string $sku, float $qtyToDeduct, float $qtyAvailable)
    {
        $this->sourceCode = $sourceCode;
        $this->sku = $sku;
        $this->qtyToDeduct = $qtyToDeduct;
        $this->qtyAvailable = $qtyAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQtyToDeduct(): float
    {
        return $this->qtyToDeduct;
    }

    /**
     * @inheritdoc
     */
    public function getQtyAvailable(): float
    {
        return $this->qtyAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(
                SourceSelectionItemInterface::class
            );
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceSelectionItemExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
