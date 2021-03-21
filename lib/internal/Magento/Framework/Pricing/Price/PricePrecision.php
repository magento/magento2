<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Locale\FormatInterface;

class PricePrecision implements PricePrecisionInterface
{
    private const DEFAULT_PRECISION = 2;

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var int
     */
    private $precision;

    /**
     * @param FormatInterface $format
     */
    public function __construct(
        FormatInterface $format
    ) {
        $this->format = $format;
    }

    /**
     * @inheritDoc
     */
    public function getPrecision(): int
    {
        if ($this->precision === null) {
            $priceFormat = $this->format->getPriceFormat();

            if (is_array($priceFormat) && array_key_exists('precision', $priceFormat)) {
                $this->precision = $priceFormat['precision'];
            } else {
                $this->precision = self::DEFAULT_PRECISION;
            }
        }

        return $this->precision;
    }
}
