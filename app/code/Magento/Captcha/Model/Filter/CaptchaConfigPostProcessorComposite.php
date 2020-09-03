<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;

/**
 * Composite filter class for filtering configuration
 */
class CaptchaConfigPostProcessorComposite implements CaptchaConfigPostProcessorInterface
{
    /**
     * @var CaptchaConfigPostProcessorInterface[] $filters
     */
    private $filters = [];

    /**
     * @param CaptchaConfigPostProcessorInterface[] $filters
     */
    public function __construct(
        $filters = []
    ) {
        $this->filters = $filters;
    }

    /**
     * Loops through all leafs of the composite and calls filter method
     *
     * @param array $config
     * @return array
     */
    public function filter(array $config): array
    {
        $result = [];
        foreach ($this->filters as $filter) {
            $result = array_merge_recursive($result, $filter->filter($config));
        }
        return $result;
    }
}
