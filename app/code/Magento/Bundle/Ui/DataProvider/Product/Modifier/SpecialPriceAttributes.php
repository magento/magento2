<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use NumberFormatter;

/**
 * Modify product listing special price attributes
 */
class SpecialPriceAttributes implements ModifierInterface
{
    public const LOCALE_USING_DECIMAL_COMMA = ['nl_BE', 'nl_NL'];

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var array
     */
    private $priceAttributeList;

    /**
     * @var DirectoryCurrency
     */
    private $directoryCurrency;

    /**
     * PriceAttributes constructor.
     *
     * @param DirectoryCurrency $directoryCurrency
     * @param ResolverInterface $localeResolver
     * @param array $priceAttributeList
     */
    public function __construct(
        DirectoryCurrency $directoryCurrency,
        ResolverInterface $localeResolver,
        array $priceAttributeList = []
    ) {
        $this->directoryCurrency = $directoryCurrency;
        $this->localeResolver = $localeResolver;
        $this->priceAttributeList = $priceAttributeList;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        if (empty($data) || empty($this->priceAttributeList)) {
            return $data;
        }
        $numberFormatter = new NumberFormatter(
            $this->localeResolver->getLocale(),
            NumberFormatter::PERCENT
        );
        $numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
        foreach ($data['items'] as &$item) {
            foreach ($this->priceAttributeList as $priceAttribute) {
                if (isset($item[$priceAttribute]) && $item['type_id'] == Type::TYPE_CODE) {
                    $item[$priceAttribute] =
                        $this->directoryCurrency->format(
                            $item[$priceAttribute],
                            ['display' => CurrencyData::NO_SYMBOL],
                            false
                        );
                    if (in_array($this->localeResolver->getLocale(), self::LOCALE_USING_DECIMAL_COMMA)) {
                        $item[$priceAttribute] = str_replace(['.',','], ['','.'], $item[$priceAttribute]);
                    }
                    $item[$priceAttribute] = $numberFormatter->format($item[$priceAttribute] / 100);
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
