<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Input;

interface PurifierInterface
{
    /**
     * Purify Content from malicious code
     *
     * @param string|array $content
     * @return string|array
     */
    public function purify($content);
}
