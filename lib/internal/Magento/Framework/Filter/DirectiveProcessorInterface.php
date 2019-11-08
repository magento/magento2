<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter;

/**
 * Responsible for converting a directive data structure to relevant template output
 */
interface DirectiveProcessorInterface
{
    /**
     * Handle the directive from the template
     *
     * @param array $construction The result of the regular expression match
     * @param Template $filter The filter that is processing the template
     * @param array $templateVariables The dataset available to the template
     * @return string The rendered directive content
     */
    public function process(array $construction, Template $filter, array $templateVariables): string;

    /**
     * Return the regular expression that will be used to determine if this processor can process a directive
     *
     * @return string The regular expression including markers and flags. E.g. /foo/i
     */
    public function getRegularExpression(): string;
}
