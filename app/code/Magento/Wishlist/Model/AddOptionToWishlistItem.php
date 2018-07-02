<?php

namespace Magento\Wishlist\Model;

use Magento\Wishlist\Api\AddOptionToWishlistItemInterface;

class AddOptionToWishlistItem implements AddOptionToWishlistItemInterface
{
    /**
     * @var Item\OptionFactory
     */
    private $optionFactory;

    /**
     * AddOptionToWishlistItem constructor.
     * @param Item\OptionFactory $optionFactory
     */
    public function __construct(\Magento\Wishlist\Model\Item\OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        \Magento\Wishlist\Api\Data\ItemInterface $item,
        \Magento\Wishlist\Api\Data\OptionInterface $option
    ): \Magento\Wishlist\Api\Data\ItemInterface {
        return $option->setItem($item);
    }
}
