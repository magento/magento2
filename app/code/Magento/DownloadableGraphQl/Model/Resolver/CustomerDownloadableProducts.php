<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Data as DownloadableHelper;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Downloadable\Model\ResourceModel\Link\Collection as LinkCollection;
use Magento\Downloadable\Model\ResourceModel\Sample\Collection as SampleCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 *
 * Format for downloadable product types
 */
class CustomerDownloadableProducts implements ResolverInterface
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
     * @var SampleCollection
     */
    private $sampleCollection;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @param EnumLookup $enumLookup
     * @param DownloadableHelper $downloadableHelper
     * @param SampleCollection $sampleCollection
     * @param LinkCollection $linkCollection
     */
    public function __construct(
        EnumLookup $enumLookup,
        DownloadableHelper $downloadableHelper,
        SampleCollection $sampleCollection,
        LinkCollection $linkCollection
    ) {
        $this->enumLookup = $enumLookup;
        $this->downloadableHelper = $downloadableHelper;
        $this->sampleCollection = $sampleCollection;
        $this->linkCollection = $linkCollection;
    }

    /**
     * @inheritdoc
     *
     * Add downloadable options to configurable types
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return null|array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        die('aaaa');
        return [1];
    }
}
