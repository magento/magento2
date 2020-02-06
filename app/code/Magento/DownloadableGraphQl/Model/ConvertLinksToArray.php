<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Framework\UrlInterface;

/**
 * Convert links to array
 */
class ConvertLinksToArray
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Format links from collection as array
     *
     * @param LinkInterface[] $links
     * @return array
     */
    public function execute(array $links): array
    {
        $data = [];
        foreach ($links as $key => $link) {
            $data[$key] = [
                'id' => $link->getId(),
                'sort_order' => $link->getSortOrder(),
                'title' => $link->getTitle(),
                'sample_url' => $this->urlBuilder->getUrl(
                    'downloadable/download/linkSample',
                    ['link_id' => $link->getId()]
                ),
                'price' => $link->getPrice(),
            ];
        }
        return $data;
    }
}
