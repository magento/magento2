<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Magento escape methods
 */
class Escaper
{
    /**
     * @var \Magento\Framework\ZendEscaper
     */
    private $escaper;

    /**
     * @var string[]
     */
    private $allowedAttributes = ['id', 'class', 'href', 'target'];

    /**
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array $allowedTags
     * @return string|array
     */
    public function escapeHtml($data, $allowedTags = [])
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item, $allowedTags);
            }
        } elseif (strlen($data)) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $result = $this->escapeHtmlTagsAndAttributes($data, $allowedTags);
            } else {
                $result = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    /**
     * Escape not allowed HTML entities
     *
     * @param string $string
     * @param string[] $allowedTags
     * @return string
     */
    private function escapeHtmlTagsAndAttributes($string, $allowedTags)
    {
        $tagReplacements = [];

        $string = preg_replace_callback(
            '/<.+?>/si',
            function ($matches) use (&$tagReplacements) {
                $result = '-=replacement-' . count($tagReplacements) . '=-';
                $tagReplacements[] = $matches[0];
                return $result;
            },
            $string
        );

        foreach ($tagReplacements as $tagReplacementKey => $tagReplacement) {
            $isClosing = substr($tagReplacement, 0, 2) == '</';
            $isSelfClosing = substr($tagReplacement, -2) == '/>';

            preg_match('/<\/?(\w+)(\s|>)/', $tagReplacement, $matches);
            $tagName = $matches[1];

            if (!in_array($tagName, $allowedTags)) {
                $tagReplacements[$tagReplacementKey] = '';
                continue;
            }

            if (!$isClosing) {
                $length = strlen($tagReplacement);
                set_error_handler(
                    function($errorNumber, $errorString, $errorFile, $errorLine) {
                        throw new \Exception($errorString, $errorNumber);
                    }
                );
                try {
                    $tagReplacement = substr($tagReplacement, 1, $length - 2);
                    $tagReplacement = str_replace(
                        ['&', '<', '>'],
                        ['&amp;', '&lt;', '&gt;'],
                        $tagReplacement
                    );

                    $dom = new \DOMDocument();
                    $dom->loadHTML('<' . $tagReplacement . '>');
                } catch (\Exception $e) {
                    $tagReplacements[$tagReplacementKey] = '';
                    restore_error_handler();
                    continue;
                }
                restore_error_handler();
                $element = $dom->getElementsByTagName($tagName)[0];
                $attributes = [];
                if ($element->attributes) {
                    foreach($element->attributes as $attribute) {
                        if (in_array($attribute->name, $this->allowedAttributes)) {
                            $attributes[] = $attribute->name . '="'
                                . $this->escapeAttributeValue(
                                    $attribute->name,
                                    $attribute->value
                                )
                                . '"';
                        }
                    }
                    $tagReplacements[$tagReplacementKey] = '<' . $tagName . ' ' . implode(' ', $attributes)
                        . ($isSelfClosing ? '/>' : '>');
                }
            } else {
                $tagReplacements[$tagReplacementKey] = $isSelfClosing
                    ? '<' . $tagName . ' />' : '</' . $tagName . '>';
            }
        }

        $string = $this->escapeHtml($string);

        $string = preg_replace_callback(
            '/-=replacement-(\d)=-/si',
            function($matches) use ($tagReplacements) {
                return $tagReplacements[$matches[1]];
            },
            $string
        );

        return $string;
    }

    /**
     * Escape attribute values using escapeHtmlAttr or escapeUrl depending on the attribute
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function escapeAttributeValue($name, $value)
    {
        return $name == 'href' ? $this->escapeUrl($value) : $this->escapeHtml($value);
    }

    /**
     * Escape a string for the HTML attribute context
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if ($escapeSingleQuote) {
            return $this->getEscaper()->escapeHtmlAttr((string) $string);
        }
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
    }

    /**
     * Escape URL
     *
     * @param string $string
     * @return string
     */
    public function escapeUrl($string)
    {
        return $this->escapeHtml($this->escapeXssInUrl($string));
    }

    /**
     * Encode URL
     *
     * @param string $string
     * @return string
     */
    public function encodeUrlParam($string)
    {
        return $this->getEscaper()->escapeUrl($string);
    }

    /**
     * Escape string for the JavaScript context
     *
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        return $this->getEscaper()->escapeJs($string);
    }

    /**
     * Escape string for the CSS context
     *
     * @param string $string
     * @return string
     */
    public function escapeCss($string)
    {
        return $this->getEscaper()->escapeCss($string);
    }

    /**
     * Escape quotes in java script
     *
     * @param string|array $data
     * @param string $quote
     * @return string|array
     * @deprecated
     */
    public function escapeJsQuote($data, $quote = '\'')
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeJsQuote($item, $quote);
            }
        } else {
            $result = str_replace($quote, '\\' . $quote, $data);
        }
        return $result;
    }

    /**
     * Escape xss in urls
     * Remove `javascript:`, `vbscript:`, `data:` words from url
     *
     * @param string $data
     * @return string
     * @deprecated
     */
    public function escapeXssInUrl($data)
    {
        $pattern = '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:))|'
            . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|'
            . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';
        $result = preg_replace($pattern, ':', $data);
        return htmlspecialchars($result, ENT_COMPAT | ENT_HTML5 | ENT_HTML401, 'UTF-8', false);
    }

    /**
     * Escape quotes inside html attributes
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     * @deprecated
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\ZendEscaper
     * @deprecated
     */
    private function getEscaper()
    {
        if ($this->escaper == null) {
            $this->escaper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\ZendEscaper::class);
        }
        return $this->escaper;
    }
}
