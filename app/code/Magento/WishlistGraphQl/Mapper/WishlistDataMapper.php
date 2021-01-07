<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Mapper;

use Magento\Framework\GraphQl\Schema\Type\Enum\DataMapperInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Prepares the wishlist output as associative array
 */
class WishlistDataMapper
{
    /**
     * @var DataMapperInterface
     */
    private $enumDataMapper;

    /** 
     * @var Uid 
     */
    private $uidEncoder;

    /**
     * @param DataMapperInterface $enumDataMapper
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        DataMapperInterface $enumDataMapper,
        Uid $uidEncoder = null
    ) {
        $this->enumDataMapper = $enumDataMapper;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
    }

    /**
     * Mapping the review data
     *
     * @param Wishlist $wishlist
     *
     * @return array
     */
    public function map(Wishlist $wishlist): array
    {
        return [
            'id' => $wishlist->getId(),
            'uid' => $this->uidEncoder->encode($wishlist->getId()),
            'sharing_code' => $wishlist->getSharingCode(),
            'updated_at' => $wishlist->getUpdatedAt(),
            'items_count' => $wishlist->getItemsCount(),
            'name' => $wishlist->getName(),
            'visibility' => $this->getMappedVisibility((int) $wishlist->getVisibility()),
            'model' => $wishlist,
        ];
    }

    /**
     * Get wishlist mapped visibility
     *
     * @param int $visibility
     *
     * @return string|null
     */
    private function getMappedVisibility(int $visibility): ?string
    {
        if ($visibility === null) {
            return null;
        }

        $visibilityEnums = $this->enumDataMapper->getMappedEnums('WishlistVisibilityEnum');

        return isset($visibilityEnums[$visibility]) ? strtoupper($visibilityEnums[$visibility]) : null;
    }
}