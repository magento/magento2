<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Model\SynchronizerIdentitiesPool;
use Psr\Log\LoggerInterface;

/**
 * Batch Synchronize content with assets
 */
class SynchronizeIdentities implements SynchronizeIdentitiesInterface
{
    private const LAST_EXECUTION_TIME_CODE = 'media_content_last_execution';

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var SynchronizerIdentitiesPool
     */
    private $synchronizerIdentitiesPool;

    /**
     * @var RemoveObsoleteContentAsset
     */
    private $removeObsoleteContent;

    /**
     * @param RemoveObsoleteContentAsset $removeObsoleteContent
     * @param DateTimeFactory $dateFactory
     * @param FlagManager $flagManager
     * @param LoggerInterface $log
     * @param SynchronizerIdentitiesPool $synchronizerIdentitiesPool
     */
    public function __construct(
        RemoveObsoleteContentAsset $removeObsoleteContent,
        DateTimeFactory $dateFactory,
        FlagManager $flagManager,
        LoggerInterface $log,
        SynchronizerIdentitiesPool $synchronizerIdentitiesPool
    ) {
        $this->removeObsoleteContent = $removeObsoleteContent;
        $this->dateFactory = $dateFactory;
        $this->flagManager = $flagManager;
        $this->log = $log;
        $this->synchronizerIdentitiesPool = $synchronizerIdentitiesPool;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $mediaContentIdentities): void
    {
        $failed = [];

        foreach ($this->synchronizerIdentitiesPool->get() as $name => $synchronizers) {
            try {
                $synchronizers->execute($mediaContentIdentities);
            } catch (\Exception $exception) {
                $this->log->critical($exception);
                $failed[] = $name;
            }
        }

        if (!empty($failed)) {
            throw new LocalizedException(
                __(
                    'Failed to execute the following content synchronizers: %synchronizers',
                    [
                        'synchronizers' => implode(', ', $failed)
                    ]
                )
            );
        }

        $this->setLastExecutionTime();
        $this->removeObsoleteContent->execute();
    }

    /**
     * Set last synchronizer execution time
     */
    private function setLastExecutionTime(): void
    {
        $currentTime = $this->dateFactory->create()->gmtDate();
        $this->flagManager->saveFlag(self::LAST_EXECUTION_TIME_CODE, $currentTime);
    }
}
