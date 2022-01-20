<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use NumberFormatter;
use Zend_Currency;
use Zend_Currency_Exception;

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
     * @throws NoSuchEntityException
     * @throws Zend_Currency_Exception
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
                            ['display' => Zend_Currency::NO_SYMBOL],
                            false
                        );
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
