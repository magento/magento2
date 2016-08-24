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
        $allowedAttributes = ['id', 'class', 'href'];

        $allowedTags = implode('|', $allowedTags);
        $allowedAttributes = implode('|', $allowedAttributes);

        $attributeReplacements = [];

        $string = preg_replace_callback(
            '/(' . $allowedAttributes . ')="(.*?)"/si',
            function ($matches) use (&$attributeReplacements) {
                $result = $matches[1] . '=-=quote=--=attribute-value-' . count($attributeReplacements) . '=--=quote=-';
                $attributeReplacements[] = [
                    'name' => $matches[1],
                    'value' => $matches[2]
                ];
                return $result;
            },
            $string
        );

        $string = preg_replace(
            '/<([\/\s\r\n]*)(' . $allowedTags . ')([^>]*)([\/\s\r\n]*)>/si',
            '##$1$2$3$4##',
            $string
        );
        $string = $this->escapeHtml($string);
        $string = preg_replace(
            '/##([\/\s\r\n]*)(' . $allowedTags . ')([^##]*)([\/\s\r\n]*)##/si',
            '<$1$2$3$4>',
            $string
        );

        $attributeReplacements = $this->escapeAttributeValues($attributeReplacements);

        $string = preg_replace_callback(
            '/-=quote=--=attribute-value-(\d)=--=quote=-/si',
            function($matches) use (&$attributeReplacements) {
                return '"' . $attributeReplacements[$matches[1]]['value'] . '"';
            },
            $string
        );

        return $string;
    }

    /**
     * Escape attribute values using escapeHtmlAttr and escapeUrl depending on the attribute. $attributes has the
     * following structure [['name' => 'id', 'value' => 'identifier'], ['name' => 'href', 'value' => 'http://abc.com/']]
     *
     * @param array $attributes
     * @return array
     */
    private function escapeAttributeValues($attributes)
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute['name'] == 'href') {
                $attributes[$key]['value'] = $this->escapeHtml($attribute['value']);
            } else {
                $attributes[$key]['value'] = $this->escapeHtmlAttr($attribute['value']);
            }
        }
        return $attributes;
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
