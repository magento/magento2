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
     * @param array $allowedTags
     * @param array $allowedAttributes
     * @param array $attributesAllowedByTags
     */
    public function __construct(array $allowedTags, array $allowedAttributes = [], array $attributesAllowedByTags = [])
    {
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
    }

    /**
     * @inheritDoc
     */
    public function validate(string $content): void
    {
        $dom = $this->loadHtml($content);
        $xpath = new \DOMXPath($dom);

        //Validating tags
        $found = $xpath->query(
            $query='//*['
            . implode(
                ' and ',
                array_map(
                    function (string $tag): string {
                        return "name() != '$tag'";
                    },
                    array_merge($this->allowedTags, ['body', 'html'])
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
                $allowed = $this->allowedAttributes;
                if (!empty($this->attributesAllowedByTags[$tag])) {
                    $allowed = array_unique(array_merge($allowed, $this->attributesAllowedByTags[$tag]));
                }
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
     * Load DOM.
     *
     * @param string $content
     * @return \DOMDocument
     * @throws ValidationException
     */
    private function loadHtml(string $content): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = true;
        set_error_handler(
            function () use (&$loaded) {
                $loaded = false;
            }
        );
        $loaded = $dom->loadHTML("<html><body>$content</body></html>");
        restore_error_handler();
        if (!$loaded) {
            throw new ValidationException(__('Invalid HTML content provided'));
        }

        return $dom;
    }
}
