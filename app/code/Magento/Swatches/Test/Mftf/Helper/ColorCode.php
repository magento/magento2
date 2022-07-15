<?php

namespace Magento\Swatches\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Class ColorCode provides an ability to color code manipulation.
 */
class ColorCode extends Helper
{
    /**
     * Color code check.
     *
     * @param string $colorCode
     * @return string
     */
    public function colorCode(string $colorCode): string
    {
        $colorCode = str_replace('background: #', '', $colorCode);
        if (!preg_match('/^[a-f0-9]{6}$/i', $colorCode) ) {
            return $colorCode;
        }

        $length   = strlen($colorCode);
        $rgbColorCode['r'] = hexdec($length == 6 ? substr($colorCode, 0, 2) : ($length == 3 ? str_repeat(substr($colorCode, 0, 1), 2) : 0));
        $rgbColorCode['g'] = hexdec($length == 6 ? substr($colorCode, 2, 2) : ($length == 3 ? str_repeat(substr($colorCode, 1, 1), 2) : 0));
        $rgbColorCode['b'] = hexdec($length == 6 ? substr($colorCode, 4, 2) : ($length == 3 ? str_repeat(substr($colorCode, 2, 1), 2) : 0));

        return 'background: rgb('.implode(', ', $rgbColorCode).');';
    }
}
