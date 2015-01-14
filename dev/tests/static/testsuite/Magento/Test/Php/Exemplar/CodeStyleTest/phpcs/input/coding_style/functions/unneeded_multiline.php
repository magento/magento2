<?php
/**
 * Function with parameters.
 * With long description.
 *
 * @param string|null $a
 * @param bool $b
 * @return string
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */

// @codingStandardsIgnoreFile

function thereGoesFunc($a,
    $b
) {
    if ($a === null) {
        $a = 'Stranger';
    }
    return 'Hello, ' . $a . '!';
}
