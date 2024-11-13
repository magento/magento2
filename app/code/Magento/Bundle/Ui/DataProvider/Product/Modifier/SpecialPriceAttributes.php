<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\NumberFormatterFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use NumberFormatter;

/**
 * Modify product listing special price attributes
 */
class SpecialPriceAttributes implements ModifierInterface
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var array
     */
    private $priceAttributeList;

    /**
     * @var NumberFormatterFactory
     */
    private $numberFormatterFactory;

    /**
     * PriceAttributes constructor.
     *
     * @param DirectoryCurrency $directoryCurrency
     * @param ResolverInterface $localeResolver
     * @param array $priceAttributeList
     * @param NumberFormatterFactory|null $numberFormatterFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DirectoryCurrency $directoryCurrency,
        ResolverInterface $localeResolver,
        array $priceAttributeList = [],
        ?NumberFormatterFactory $numberFormatterFactory = null
    ) {
        $this->localeResolver = $localeResolver;
        $this->priceAttributeList = $priceAttributeList;
        $this->numberFormatterFactory = $numberFormatterFactory
            ?? ObjectManager::getInstance()->get(NumberFormatterFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        if (empty($data) || empty($this->priceAttributeList)) {
            return $data;
        }
        $numberFormatter = $this->numberFormatterFactory->create([
            'locale' => $this->localeResolver->getLocale(),
            'style' => NumberFormatter::PERCENT
        ]);
        $numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 6);
        foreach ($data['items'] as &$item) {
            foreach ($this->priceAttributeList as $priceAttribute) {
                if (isset($item[$priceAttribute]) && $item[ProductInterface::TYPE_ID] === Type::TYPE_CODE) {
                    $item[$priceAttribute] = $numberFormatter->format((float) $item[$priceAttribute] / 100);
                }
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
