<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Escape helper methods.
 */
class EscapeHelper extends AbstractHelper
{
    /**
     * @var \Zend\Escaper\Escaper
     */
    private $escaper;

    /**
     * Creates helper instance.
     * 
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->escaper = new \Zend\Escaper\Escaper;
    }

    /**
     * Escape a string for the HTML attribute context.
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if ($escapeSingleQuote) {
            return $this->escaper->escapeHtmlAttr((string) $string);
        }

        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
    }

    /**
     * Escape string for the JavaScript context.
     *
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        return preg_replace_callback(
            '/[^a-z0-9,\._]/iSu',
            function ($matches) {
                $chr = $matches[0];
                if (strlen($chr) != 1) {
                    $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $chr = ($chr === false) ? '' : $chr;
                }

                return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
            },
            $string
        );
    }
}
