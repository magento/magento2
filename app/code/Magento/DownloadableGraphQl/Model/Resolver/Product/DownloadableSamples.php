<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

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
     * @param SampleFactory $sampleFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        EnumLookup $enumLookup,
        SampleFactory $sampleFactory,
        UrlInterface $urlBuilder
    ) {
        $this->enumLookup = $enumLookup;
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
    public function resolve(
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
        foreach ($samples as $sampleKey => $sample) {
            /** @var Sample $sample */
            $resultData[$sampleKey]['id'] = $sample->getId();
            $resultData[$sampleKey]['title'] = $sample->getTitle();
            $resultData[$sampleKey]['sort_order'] = $sample->getSortOrder();
            $resultData[$sampleKey]['sample_type'] = $this->enumLookup->getEnumValueFromField(
                'DownloadableFileTypeEnum',
                $sample->getSampleType()
            );
            $resultData[$sampleKey]['sample_file'] = $sample->getSampleFile();
            $resultData[$sampleKey]['sample_url'] = $this->urlBuilder->getUrl(
                'downloadable/download/sample',
                ['sample_id' => $sample->getId()]
            );
        }
        return $resultData;
    }
}
