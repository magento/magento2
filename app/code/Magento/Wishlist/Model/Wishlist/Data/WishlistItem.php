<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\Data;

/**
 * DTO represents Wishlist Item data
 *
 * @api
 */
class WishlistItem
{
    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string|null
     */
    private $sku;

    /**
     * @var string
     */
    private $parentSku;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var SelectedOption[]
     */
    private $selectedOptions;

    /**
     * @var EnteredOption[]
     */
    private $enteredOptions;

    /**
     * @param float $quantity
     * @param string|null $sku
     * @param string|null $parentSku
     * @param int|null $id
     * @param string|null $description
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     */
    public function __construct(
        float $quantity,
        ?string $sku = null,
        ?string $parentSku = null,
        ?int $id = null,
        ?string $description = null,
        ?array $selectedOptions = null,
        ?array $enteredOptions = null
    ) {
        $this->quantity = $quantity;
        $this->sku = $sku;
        $this->parentSku = $parentSku;
        $this->id = $id;
        $this->description = $description;
        $this->selectedOptions = $selectedOptions;
        $this->enteredOptions = $enteredOptions;
    }

    /**
     * Get wishlist item id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get wishlist item description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get sku
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * Get parent sku
     *
     * @return string|null
     */
    public function getParentSku(): ?string
    {
        return $this->parentSku;
    }

    /**
     * Get selected options
     *
     * @return SelectedOption[]|null
     */
    public function getSelectedOptions(): ?array
    {
        return $this->selectedOptions;
    }

    /**
     * Get entered options
     *
     * @return EnteredOption[]|null
     */
    public function getEnteredOptions(): ?array
    {
        return $this->enteredOptions;
    }
}
