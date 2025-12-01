<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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
