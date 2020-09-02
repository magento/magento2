<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Helper\SecureHtmlRender;

/**
 * Tag data to render.
 */
class TagData
{
    /**
     * @var string
     */
    private $tag;

    /**
     * @var string[]
     */
    private $attributes;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var bool
     */
    private $textContent;

    /**
     * @param string $tag
     * @param string[] $attributes
     * @param string|null $content
     * @param bool $textContent
     */
    public function __construct(string $tag, array $attributes, ?string $content, bool $textContent)
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->content = $content;
        $this->textContent = $textContent;
    }

    /**
     * Tag name (like "style", "script" etc)
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Attributes list, not escaped.
     *
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Text or HTML inner content.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Is the content to be treated as text or HTML?
     *
     * @return bool
     */
    public function isTextContent(): bool
    {
        return $this->textContent;
    }
}
