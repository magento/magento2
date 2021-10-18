<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\UserToken;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\UserTokenValidatorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DtUtil;

class ExpirationValidator implements UserTokenValidatorInterface
{
    /**
     * @var DtUtil
     */
    private $datetimeUtil;

    /**
     * @param DtUtil $datetimeUtil
     */
    public function __construct(DtUtil $datetimeUtil)
    {
        $this->datetimeUtil = $datetimeUtil;
    }

    /**
     * @inheritDoc
     */
    public function validate(UserToken $token): void
    {
        if ($token->getData()->getExpires()->getTimestamp() <= $this->datetimeUtil->gmtTimestamp()) {
            throw new AuthorizationException(__('Consumer key has expired'));
        }
    }
}
