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
     * ArbitraryPayload constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
