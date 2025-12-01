<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Helper;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Test helper for Block classes with custom methods
 *
 */
class AbstractBlockTestHelper extends AbstractBlock
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->data['html_result'] ?? '';
    }

    /**
     * Custom render method for testing (used in pricing renderers)
     *
     * @param string $type
     * @param mixed $product
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render($type, $product, $arguments = [])
    {
        return $this->data['render_result'] ?? '';
    }

    /**
     * Set render result for testing
     *
     * @param string $result
     * @return self
     */
    public function setRenderResult(string $result): self
    {
        $this->data['render_result'] = $result;
        return $this;
    }
}
