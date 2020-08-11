<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Framework\Stdlib\StringUtils;

class RtlTextHandler
{
    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @param StringUtils $stringUtils
     */
    public function __construct(StringUtils $stringUtils)
    {
        $this->stringUtils = $stringUtils;
    }

    /**
     * Detect an input string is Arabic
     *
     * @param string $subject
     * @return bool
     */
    public function isRtlText(string $subject): bool
    {
        return (preg_match('/[\p{Arabic}\p{Hebrew}]/u', $subject) > 0);
    }

    /**
     * Reverse text with Arabic characters
     *
     * @param string $string
     * @return string
     */
    public function reverseRtlText(string $string): string
    {
        $splitText = explode(' ', $string);
        $splitTextAmount = count($splitText);

        for ($i = 0; $i < $splitTextAmount; $i++) {
            if ($this->isRtlText($splitText[$i])) {
                for ($j = $i + 1; $j < $splitTextAmount; $j++) {
                    $tmp = $this->isRtlText($splitText[$j])
                        ? $this->stringUtils->strrev($splitText[$j]) : $splitText[$j];
                    $splitText[$j] = $this->isRtlText($splitText[$i])
                        ? $this->stringUtils->strrev($splitText[$i]) : $splitText[$i];
                    $splitText[$i] = $tmp;
                }
            }
        }

        return implode(' ', $splitText);
    }
}
