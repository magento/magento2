<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;

/**
 * Create value-object \Magento\Framework\Phrase
 *
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * phpcs:disable Squiz.Functions.GlobalFunction
 * @param array $argc
 * @return Phrase
 */
function __(...$argc)
{
    $text = array_shift($argc);
    if (!empty($argc) && is_array($argc[0])) {
        $argc = $argc[0];
    }

    return ObjectManager::getInstance()->create(
        Phrase::class,
        [
            'text' => $text,
            'arguments' => $argc
        ]
    );
}
