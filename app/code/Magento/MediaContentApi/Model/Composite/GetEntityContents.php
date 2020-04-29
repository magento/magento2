<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model\Composite;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;

/**
 * Get concatenated content for all store views
 */
class GetEntityContents implements GetEntityContentsInterface
{
    /**
     * @var GetEntityContentsInterface[]
     */
    private $items;

    /**
     * @param GetEntityContentsInterface[] $items
     */
    public function __construct(
        $items = []
    ) {
        foreach ($items as $item) {
            if (!$item instanceof GetEntityContentsInterface) {
                throw new \InvalidArgumentException(
                    __('GetContent items must implement %1.', GetEntityContentsInterface::class)
                );
            }
        }

        $this->items = $items;
    }

    /**
     * Get concatenated content for the content identity
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return string[]
     */
    public function execute(ContentIdentityInterface $contentIdentity): array
    {
        if (isset($this->items[$contentIdentity->getEntityType()])) {
            return $this->items[$contentIdentity->getEntityType()]->execute($contentIdentity);
        }
        return [];
    }
}
