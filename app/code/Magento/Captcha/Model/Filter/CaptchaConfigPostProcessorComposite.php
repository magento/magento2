<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\CaptchaConfigPostProcessorInterface;

/**
 * Composite class for post processing captcha configuration
 */
class CaptchaConfigPostProcessorComposite implements CaptchaConfigPostProcessorInterface
{
    /**
     * @var CaptchaConfigPostProcessorInterface[] $processors
     */
    private $processors = [];

    /**
     * @param CaptchaConfigPostProcessorInterface[] $processors
     */
    public function __construct(
        $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * Loops through all leafs of the composite and calls process method
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array
    {
        $result = [];
        foreach ($this->processors as $processor) {
            $result = array_merge_recursive($result, $processor->process($config));
        }
        return $result;
    }
}
