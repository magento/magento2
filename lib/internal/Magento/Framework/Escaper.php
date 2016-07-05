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
    const HTMLSPECIALCHARS_FLAG = ENT_QUOTES | ENT_SUBSTITUTE;

    /**
     * @var \Zend\Escaper\Escaper
     */
    private $zendEscaper;

    /**
     * @param void
     * @return \Magento\Framework\Escaper
     */
    private function getZendEscaper()
    {
        if ($this->zendEscaper === null) {
            $this->zendEscaper =
                \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\ZendEscaper::class);
        }
        return $this->zendEscaper;
    }

    /**
     * Escape html entities
     *
     * @param  string|array $data
     * @param  array $allowedTags
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
            if (is_array($allowedTags) and !empty($allowedTags)) {
                $allowed = implode('|', $allowedTags);
                $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                $result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8', false);
                $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
            } else {
                $result = htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    /**
     * Escape a string for the HTML Attribute context.
     *
     * @param string $data
     * @return string
     */
    public function escapeHtmlAttr($data) {
        return $this->zendEscaper->escapeHtmlAttr($data);
    }

    /**
     * Escape html entities in url
     *
     * @param string $data
     * @return string
     */
    public function escapeUrl($data)
    {
        return htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
    }

    /**
     * Escapes data for the Javascript context
     *
     * @param string|array $data
     * @return array|string
     */
    public function escapeJs($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->getZendEscaper()->escapeJs($item);
            }
        } else {
            $result = $this->getZendEscaper()->escapeJs($data);
        }
        return $result;
    }

    /**
     * Escape quotes in java script
     *
     * @param string|array $data
     * @param string $quote
     * @return string|array
     *
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
     *
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
     *
     * @deprecated
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }
}
