<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

/**
 * Class containing secure customer data that cannot be exposed as part of \Magento\Customer\Api\Data\CustomerInterface
 *
 * @method string getRpToken()
 * @method string getRpTokenCreatedAt()
 * @method string getPasswordHash()
 * @method string getDeleteable()
 * @method setRpToken(string $rpToken)
 * @method setRpTokenCreatedAt(string $rpTokenCreatedAt)
 * @method setPasswordHash(string $hashedPassword)
 * @method setDeleteable(bool $deleteable)
 * @method setFailuresNum(int $failureNum)
 * @method setFirstFailure(?string $firstFailure)
 * @method setLockExpires(?string $lockExpires)
 */
class CustomerSecure extends \Magento\Framework\DataObject
{
}
