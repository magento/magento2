<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Psr\Log\LoggerInterface;

/**
 * Resolver fetches downloadable product samples and formats it according to the GraphQL schema.
 */
class DownloadableSamples implements ResolverInterface
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
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * DownloadableSamples constructor.
     *
     * @param EnumLookup $enumLookup
     * @param LoggerInterface $logger
     * @param SampleFactory $sampleFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        EnumLookup $enumLookup,
        LoggerInterface $logger,
        SampleFactory $sampleFactory,
        UrlInterface $urlBuilder
    ) {
        $this->enumLookup = $enumLookup;
        $this->logger = $logger;
        $this->sampleFactory = $sampleFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Fetches downloadable product samples and formats it according to the GraphQL schema.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function resolve(// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var QuoteItem $quoteItem */
        $quoteItem = $value['model'];

        /** @var Product $product */
        $product = $quoteItem->getProduct();

        if (!in_array($product->getTypeId(), ['downloadable', 'virtual'])) {
            throw new GraphQlInputException(
                __('Wrong product type. Links are available for Downloadable and Virtual product types')
            );
        }

        if ($field->getName() != 'downloadable_product_samples') {
            throw new GraphQlInputException(
                __('Incorrect field name. Use "downloadable_product_samples" to retrieve links')
            );
        }

        $samples = $this->sampleFactory->create()->getResourceCollection();
        $samples->addTitleToResult($product->getStoreId())
            ->addProductToFilter($product->getId());

        $data = $this->formatSamples(
            $samples
        );

        return $data;
    }

    /**
     * Format links from collection as array
     *
     * @param Collection $samples
     * @return array
     * @throws RuntimeException
     */
    private function formatSamples(Collection $samples) : array
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
     * @throws RuntimeException
     */
    protected function getSampleUrl(Sample $sample): string
    {
        return $this->enumLookup->getEnumValueFromField('DownloadableFileTypeEnum', $sample->getSampleType());
    }

    /**
     * Returns sample type
     *
     * @param Sample $sample
     * @return string
     */
    private function getSampleType(Sample $sample): string
    {
        return $this->urlBuilder->getUrl(
            'downloadable/download/sample',
            [
                'sample_id' => $sample->getId(),
            ]
        );
    }
}
