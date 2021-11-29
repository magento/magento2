<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Payload;

use Magento\Framework\Jwt\PayloadInterface;

class ArbitraryPayload implements PayloadInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @param string $content
     * @param string|null $type
     */
    public function __construct(string $content, ?string $type = null)
    {
        $this->content = $content;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return $this->type;
    }
}
