<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\CaptchaConfigFilterInterface;

/**
 * Composite filter class for filtering configuration
 */
class CaptchaConfigFilterComposite implements CaptchaConfigFilterInterface
{
    /**
     * @var CaptchaConfigFilterInterface[] $filters
     */
    private $filters = [];

    /**
     * @param CaptchaConfigFilterInterface[] $filters
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
    public function filter($config): array
    {
        $result = [];
        foreach ($this->filters as $filter) {
            $result = array_merge_recursive($result, $filter->filter($config));
        }
        return $result;
    }
}
