<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Validator\HTML;

use Magento\Framework\Validation\ValidationException;

/**
 * Validates user HTML based on configuration.
 */
class ConfigurableWYSIWYGValidator implements WYSIWYGValidatorInterface
{
    /**
     * @var string
     */
    private static string $xssFiltrationPattern =
        '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:)|(script)|(alert\())|'
        . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|'
        . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';

    /**
     * @var string
     */
    private static string $contentFiltrationPattern = "/(<body)/i";

    /**
     * @var string[]
     */
    private $allowedTags;

    /**
     * @var string[]
     */
    private $allowedAttributes;

    /**
     * @var string[]
     */
    private $attributesAllowedByTags;

    /**
     * @var AttributeValidatorInterface[][]
     */
    private $attributeValidators;

    /**
     * @var TagValidatorInterface[][]
     */
    private $tagValidators;

    /**
     * @param string[] $allowedTags
     * @param string[] $allowedAttributes
     * @param string[][] $attributesAllowedByTags
     * @param AttributeValidatorInterface[][] $attributeValidators
     * @param TagValidatorInterface[][] $tagValidators
     */
    public function __construct(
        array $allowedTags,
        array $allowedAttributes = [],
        array $attributesAllowedByTags = [],
        array $attributeValidators = [],
        array $tagValidators = []
    ) {
        if (empty(array_filter($allowedTags))) {
            throw new \InvalidArgumentException('List of allowed HTML tags cannot be empty');
        }
        $this->allowedTags = array_unique($allowedTags);
        $this->allowedAttributes = array_unique($allowedAttributes);
        $this->attributesAllowedByTags = array_filter(
            $attributesAllowedByTags,
            function (string $tag) use ($allowedTags): bool {
                return in_array($tag, $allowedTags, true);
            },
            ARRAY_FILTER_USE_KEY
        );
        $this->attributeValidators = $attributeValidators;
        $this->tagValidators = $tagValidators;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $content): void
    {
        if (mb_strlen($content) === 0) {
            return;
        }
        $dom = $this->loadHtml($content);
        $xpath = new \DOMXPath($dom);

        $this->validateConfigured($xpath);
        $this->callAttributeValidators($xpath);
        $this->callTagValidators($xpath);
        $this->validateAttributeValue($xpath);
    }

    /**
     * Check declarative restrictions
     *
     * @param \DOMXPath $xpath
     * @return void
     * @throws ValidationException
     */
    private function validateConfigured(\DOMXPath $xpath): void
    {
        //Validating tags
        $this->allowedTags = array_merge($this->allowedTags, ["body", "html"]);
        $found = $xpath->query(
            '//*['
            . implode(
                ' and ',
                array_map(
                    function (string $tag): string {
                        return "name() != '$tag'";
                    },
                    $this->allowedTags
                )
            )
            .']'
        );
        if (count($found)) {
            throw new ValidationException(
                __('Allowed HTML tags are: %1', implode(', ', $this->allowedTags))
            );
        }

        //Validating attributes
        if ($this->attributesAllowedByTags) {
            foreach ($this->allowedTags as $tag) {
                $allowed = [$this->allowedAttributes];
                if (!empty($this->attributesAllowedByTags[$tag])) {
                    $allowed[] = $this->attributesAllowedByTags[$tag];
                }
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $allowed = array_unique(array_merge(...$allowed));
                $allowedQuery = '';
                if ($allowed) {
                    $allowedQuery = '['
                        . implode(
                            ' and ',
                            array_map(
                                function (string $attribute): string {
                                    return "name() != '$attribute'";
                                },
                                $allowed
                            )
                        )
                        .']';
                }
                $found = $xpath->query("//$tag/@*$allowedQuery");
                if (count($found)) {
                    throw new ValidationException(
                        __('Allowed HTML attributes for tag "%1" are: %2', $tag, implode(',', $allowed))
                    );
                }
            }
        } else {
            $allowed = '';
            if ($this->allowedAttributes) {
                $allowed = '['
                    . implode(
                        ' and ',
                        array_map(
                            function (string $attribute): string {
                                return "name() != '$attribute'";
                            },
                            $this->allowedAttributes
                        )
                    )
                    .']';
            }
            $found = $xpath->query("//@*$allowed");
            if (count($found)) {
                throw new ValidationException(
                    __('Allowed HTML attributes are: %1', implode(',', $this->allowedAttributes))
                );
            }
        }
    }

    /**
     * Validate allowed HTML attributes' content.
     *
     * @param \DOMXPath $xpath
     * @throws ValidationException
     * @return void
     */
    private function callAttributeValidators(\DOMXPath $xpath): void
    {
        if ($this->attributeValidators) {
            foreach ($this->attributeValidators as $attr => $validators) {
                $found = $xpath->query("//@*[name() = '$attr']");
                foreach ($found as $attribute) {
                    foreach ($validators as $validator) {
                        $validator->validate($attribute->parentNode->tagName, $attribute->name, $attribute->value);
                    }
                }
            }
        }
    }

    /**
     * Validate allowed tags.
     *
     * @param \DOMXPath $xpath
     * @return void
     * @throws ValidationException
     */
    private function callTagValidators(\DOMXPath $xpath): void
    {
        if ($this->tagValidators) {
            foreach ($this->tagValidators as $tag => $validators) {
                $found = $xpath->query("//*[name() = '$tag']");
                /** @var \DOMElement $tagNode */
                foreach ($found as $tagNode) {
                    $attributes = [];
                    if ($tagNode->hasAttributes()) {
                        /** @var \DOMAttr $attributeNode */
                        foreach ($tagNode->attributes as $attributeNode) {
                            $attributes[$attributeNode->name] = $attributeNode->value;
                        }
                    }
                    foreach ($validators as $validator) {
                        $validator->validate($tagNode->tagName, $attributes, $tagNode->textContent, $this);
                    }
                }
            }
        }
    }

    /**
     * Load DOM.
     *
     * @param string $content
     * @return \DOMDocument
     * @throws ValidationException
     */
    private function loadHtml(string $content): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        set_error_handler(
            function () use (&$loaded) {
                $loaded = false;
            }
        );
        $matches = [];
        preg_match_all(self::$contentFiltrationPattern, $content, $matches);
        $loaded = !(count($matches[0]) > 1) && $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED);
        restore_error_handler();
        if (!$loaded) {
            throw new ValidationException(__('Invalid HTML content provided'));
        }

        return $dom;
    }

    /**
     * Validate values of html attributes
     *
     * @param \DOMXPath $xpath
     * @return void
     * @throws ValidationException
     */
    private function validateAttributeValue(\DOMXPath $xpath): void
    {
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            if (preg_match(self::$xssFiltrationPattern, $node->parentNode->getAttribute($node->nodeName))) {
                throw new ValidationException(
                    __('Invalid value provided for attribute %1', $node->nodeName)
                );
            }
        }
    }
}
