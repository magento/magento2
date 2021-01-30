<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Payload;

use Magento\Framework\Jwt\ClaimInterface;

class ClaimsPayload implements ClaimsPayloadInterface
{
    public const CONTENT_TYPE = 'json';

    /**
     * @var ClaimInterface[]
     */
    private $claims;

    /**
     * @param ClaimInterface[] $claims
     */
    public function __construct(array $claims)
    {
        $this->claims = $claims;
    }

    /**
     * @inheritDoc
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        $data = [];
        foreach ($this->claims as $claim) {
            $data[$claim->getName()] = $claim->getValue();
        }

        return json_encode((object)$data);
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return null;
    }
}
