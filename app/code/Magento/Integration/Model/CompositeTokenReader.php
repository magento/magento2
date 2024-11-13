<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model;

use Magento\Integration\Api\Data\UserToken;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;

/**
 * Checks multiple sources for reading a token
 */
class CompositeTokenReader implements UserTokenReaderInterface
{
    /**
     * @var UserTokenReaderInterface[]
     */
    private $readers;

    /**
     * @param UserTokenReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * @inheritDoc
     */
    public function read(string $token): UserToken
    {
        foreach ($this->readers as $reader) {
            try {
                return $reader->read($token);
            } catch (UserTokenException $exception) {
                continue;
            }
        }

        throw new UserTokenException('Composite reader could not read a token');
    }
}
