<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Magento escape methods.
 */
class Escaper extends \Zend\Escaper\Escaper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $notAllowedTags = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];

    /**
     * @var string[]
     */
    private $allowedAttributes = ['id', 'class', 'href', 'target', 'title', 'style'];

    /**
     * @var string[]
     */
    private $escapeAsUrlAttributes = ['href'];

    /**
     * @param string $encoding
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        $encoding = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        parent::__construct($encoding);
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Escape HTML entities.
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
                $result[] = $this->escapeHtml($item, $allowedTags);
            }
        } elseif (strlen($data)) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $notAllowedTags = array_intersect(
                    array_map('strtolower', $allowedTags),
                    $this->notAllowedTags
                );
                if (!empty($notAllowedTags)) {
                    $this->logger->critical(
                        'The following tag(s) are not allowed: ' . implode(', ', $notAllowedTags)
                    );
                    $allowedTags = array_diff($allowedTags, $this->notAllowedTags);
                }
                $wrapperElementId = uniqid();
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                set_error_handler(
                    function ($errorNumber, $errorString) {
                        throw new \Exception($errorString, $errorNumber);
                    }
                );
                $string = mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8');
                try {
                    $domDocument->loadHTML(
                        '<html><body id="' . $wrapperElementId . '">' . $string . '</body></html>'
                    );
                } catch (\Exception $e) {
                    restore_error_handler();
                    $this->logger->critical($e);
                }
                restore_error_handler();
                $this->removeNotAllowedTags($domDocument, $allowedTags);
                $this->removeNotAllowedAttributes($domDocument);
                $this->escapeText($domDocument);
                $this->escapeAttributeValues($domDocument);
                $result = mb_convert_encoding($domDocument->saveHTML(), 'UTF-8', 'HTML-ENTITIES');
                preg_match('/<body id="' . $wrapperElementId . '">(.+)<\/body><\/html>$/si', $result, $matches);
                return !empty($matches) ? $matches[1] : '';
            } else {
                $result = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * Remove not allowed tags.
     *
     * @param \DOMDocument $domDocument
     * @param string[] $allowedTags
     * @return void
     */
    private function removeNotAllowedTags(\DOMDocument $domDocument, array $allowedTags)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//node()[name() != \''
            . implode('\' and name() != \'', array_merge($allowedTags, ['html', 'body']))
            . '\']'
        );
        foreach ($nodes as $node) {
            if ($node->nodeName != '#text' && $node->nodeName != '#comment') {
                $node->parentNode->replaceChild($domDocument->createTextNode($node->textContent), $node);
            }
        }
    }

    /**
     * Remove not allowed attributes.
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeNotAllowedAttributes(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//@*[name() != \'' . implode('\' and name() != \'', $this->allowedAttributes) . '\']'
        );
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }
    }

    /**
     * Escape text
     *
     * @param \DOMDocument $domDocument.
     * @return void
     */
    private function escapeText(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->nodeValue = $this->escapeHtml($node->nodeValue);
        }
    }

    /**
     * Escape attribute values.
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escapeAttributeValues(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $this->escapeAttributeValue(
                $node->nodeName,
                $node->parentNode->getAttribute($node->nodeName)
            );
            $node->parentNode->setAttribute($node->nodeName, $value);
        }
    }

    /**
     * Escape attribute value using escapeHtml or escapeUrl.
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function escapeAttributeValue($name, $value)
    {
        return in_array($name, $this->escapeAsUrlAttributes) ? $this->escapeUrl($value) : $this->escapeHtml($value);
    }

    /**
     * Escape URL.
     *
     * @param string $data
     * @return string
     */
    public function escapeUrl($data)
    {
        return $this->escapeHtml($this->escapeXssInUrl($data));
    }

    /**
     * Escape quotes in java script
     *
     * @param string|array $data
     * @param string $quote
     * @return string|array
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
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }
}
