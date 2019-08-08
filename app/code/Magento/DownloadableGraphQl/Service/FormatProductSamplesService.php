<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Service;

use Exception;
use Magento\Downloadable\Model\Sample;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * Convert collection of samples to formatted array
 */
class FormatProductSamplesService
{
    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * FormatProductSamplesService constructor.
     *
     * @param EnumLookup $enumLookup
     * @param LoggerInterface $logger
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        EnumLookup $enumLookup,
        LoggerInterface $logger,
        UrlInterface $urlBuilder
    ) {
        $this->enumLookup = $enumLookup;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Returns an array of formatted downloadable samples
     *
     * @param Collection $samples
     * @return array
     */
    public function execute(Collection $samples): array
    {
        $resultData = [];

        try {
            /** @var Sample $sample */
            foreach ($samples as $sampleKey => $sample) {
                $resultData[$sampleKey] = [
                    'id' => $sample->getId(),
                    'title' => $sample->getTitle(),
                    'sort_order' => $sample->getSortOrder(),
                    'sample_type' => $this->getSampleType($sample),
                    'sample_file' => $sample->getSampleFile(),
                    'sample_url' => $this->getSampleUrl($sample),
                ];
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        return $resultData;
    }

    /**
     * Returns URL of sample
     *
     * @param Sample $sample
     * @return string
     */
    private function getSampleType(Sample $sample): string
    {
        try {
            $sampleUrl = $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sample->getSampleType());
        } catch (Exception $e) {
            $sampleUrl = '';
            $this->logger->critical($e);
        }

        return $sampleUrl;
    }

    /**
     * Returns sample type
     *
     * @param Sample $sample
     * @return string
     */
    private function getSampleUrl(Sample $sample): string
    {
        try {
            $sampleType = $this->urlBuilder->getUrl(
                'downloadable/download/sample',
                [
                    'sample_id' => $sample->getId(),
                ]
            );
        } catch (Exception $e) {
            $sampleType = '';
            $this->logger->critical($e);
        }

        return $sampleType;
    }
}
