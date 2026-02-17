<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;

/**
 * Class QuoteDataConfigFilter used for filtering config quote data based on filter list
 */
class QuoteDataConfigFilter implements CaptchaConfigPostProcessorInterface
{
    /**
     * @var array $filterList
     */
    private $filterList;

    /**
     * @param array $filterList
     */
    public function __construct(
        array $filterList = []
    ) {
        $this->filterList = $filterList;
    }

    /**
     * Filters the quote config with values from a filter list
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array
    {
        foreach ($this->filterList as $filterKey) {
            /** @var string $filterKey */
            if (isset($config['quoteData']) && array_key_exists($filterKey, $config['quoteData'])) {
                unset($config['quoteData'][$filterKey]);
            }
        }
        return $config;
    }
}
