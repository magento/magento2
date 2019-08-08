<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\SampleFactory;
use Magento\DownloadableGraphQl\Service\FormatProductSamplesService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Resolver fetches downloadable product samples and formats it according to the GraphQL schema.
 */
class DownloadableSamples implements ResolverInterface
{
    /**
     * @var FormatProductSamplesService
     */
    private $formatSamplesService;

    /**
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     * DownloadableSamples constructor.
     *
     * @param FormatProductSamplesService $formatProductSamplesService
     * @param SampleFactory $sampleFactory
     */
    public function __construct(
        FormatProductSamplesService $formatProductSamplesService,
        SampleFactory $sampleFactory
    ) {
        $this->formatSamplesService = $formatProductSamplesService;
        $this->sampleFactory = $sampleFactory;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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

        $data = $this->formatSamplesService->execute($samples);

        return $data;
    }
}
