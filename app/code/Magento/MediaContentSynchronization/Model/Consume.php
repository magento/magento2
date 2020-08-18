<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;

/**
 * Media content synchronization queue consumer.
 */
class Consume
{
    /**
     * @var SynchronizeInterface
     */
    private $synchronize;

    /**
     * @var SynchronizeIdentitiesInterface
     */
    private $synchronizeIdentities;

    /**
     * @param SynchronizeInterface $synchronize
     * @param SynchronizeIdentitiesInterface $synchronizeIdentities
     */
    public function __construct(
        SynchronizeInterface $synchronize,
        SynchronizeIdentitiesInterface $synchronizeIdentities
    ) {
        $this->synchronize = $synchronize;
        $this->synchronizeIdentities = $synchronizeIdentities;
    }

    /**
     * Run media files synchronization.
     * @param string[] $identities
     * @throws LocalizedException
     */
    public function execute(array $identities) : void
    {
        if (!empty($identities)) {
            $this->synchronizeIdentities->execute($identities);
        } else {
            $this->synchronize->execute();
        }
    }
}
