<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Framework\UrlInterface;

/**
 * Convert samples to array
 */
class ConvertSamplesToArray
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
     * Format samples from collection as array
     *
     * @param SampleInterface[] $samples
     * @return array
     */
    public function execute(array $samples): array
    {
        $data = [];
        foreach ($samples as $key => $sample) {
            $data[$key] = [
                'id' => $sample->getId(),
                'sort_order' => $sample->getSortOrder(),
                'title' => $sample->getTitle(),
                'sample_url' => $this->urlBuilder->getUrl(
                    'downloadable/download/sample',
                    ['sample_id' => $sample->getId()]
                ),
            ];
        }
        return $data;
    }
}
