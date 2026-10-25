<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Create value-object \Magento\Framework\Phrase
 *
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * phpcs:disable Squiz.Functions.GlobalFunction
 * @param array $argc
 * @return \Magento\Framework\Phrase
 */
function __(...$argc)
{
    $text = array_shift($argc);
    if (!empty($argc) && is_array($argc[0])) {
        $argc = $argc[0];
    }

    return new \Magento\Framework\Phrase($text, $argc);
}
