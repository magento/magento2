<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Service;

use Exception;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Downloadable\Model\Link;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Convert collection of links to formatted array
 */
class FormatProductLinksService
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var DownloadableHelper
     */
    private $downloadableHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * FormatProductLinksService constructor.
     *
     * @param DownloadableHelper $downloadableHelper
     * @param EnumLookup $enumLookup
     * @param LoggerInterface $logger
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        DownloadableHelper $downloadableHelper,
        EnumLookup $enumLookup,
        LoggerInterface $logger,
        UrlInterface $urlBuilder
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Returns an array of formatted downloadable links
     *
     * @param array $links
     * @return array
     */
    public function execute(array $links = []): array
    {
        $resultData = [];

        try {
            foreach ($links as $linkKey => $link) {
                /** @var Link $link */
                $resultData[$linkKey] = [
                    'id' => $link->getId(),
                    'sort_order' => $link->getSortOrder(),
                    'title' => $link->getTitle(),
                    'is_shareable' => $this->downloadableHelper->getIsShareable($link),
                    'price' => $link->getPrice(),
                    'number_of_downloads' => $link->getNumberOfDownloads(),
                ];

                $linkType = $link->getLinkType();

                if ($linkType !== null) {
                    $resultData[$linkKey]['link_type'] = $this->enumLookup->getEnumValueFromField(
                        'DownloadableFileTypeEnum',
                        $linkType
                    );
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        return $resultData;
    }
}
