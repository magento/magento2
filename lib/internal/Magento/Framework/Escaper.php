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
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array $allowedTags
     * @return string|array
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } elseif (strlen($data)) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $result = $this->filterHtmlTagsAndAttributes($data, $allowedTags);
                /*
                $allowed = implode('|', $allowedTags);
                $result = preg_replace(
                    '/<([\/\s\r\n]*)(' . $allowed . '[^>]*)([\/\s\r\n]*)>/si',
                    '##$1$2$3##',
                    $data
                );
                $result = htmlspecialchars($result, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
                $result = preg_replace(
                    '/##([\/\s\r\n]*)(' . $allowed . '[^##]*)([\/\s\r\n]*)##/si',
                    '<$1$2$3>',
                    $result
                );
                */
            } else {
                $result = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    /**
     * Removes not allowed HTML tags and attributes from string
     *
     * @param string $string
     * @param string[] $allowedTags
     * @return string
     */
    private function filterHtmlTagsAndAttributes($string, $allowedTags)
    {
        $wrapperElementId = uniqid();

        $dom = new \DOMDocument();
        $dom->loadHTML('<span id="' . $wrapperElementId . '">' . $string . '</span>');
        $xpath = new \DOMXPath($dom);

        $nodes = $xpath->query('//node()[text() and name() != \'html\' and name() != \'body\' and name() != \''
            . implode('\' and name() != \'', $allowedTags) . '\']');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        $nodes = $xpath->query('//@*[name() != \'' . implode('\' and name() != \'', ['id', 'class', 'href']) . '\']');
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }

        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->textContent = $this->escapeHtml($node->textContent);
        }

        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $node->parentNode->getAttribute($node->nodeName);
            if ($node->nodeName == 'href') {
                $value = $this->escapeUrl($value);
            } else {
                $value = $this->escapeHtmlAttr($value);
            }
            $node->parentNode->setAttribute($node->nodeName, $value);
        }

        $result = mb_convert_encoding(
            $dom->saveHTML($dom->getElementById($wrapperElementId)),
            'UTF-8',
            'HTML-ENTITIES'
        );

        return substr($result, 25, strlen($result)-32);
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
