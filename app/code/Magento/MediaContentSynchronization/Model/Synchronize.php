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
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Magento\MediaContentSynchronizationApi\Model\SynchronizerPool;
use Psr\Log\LoggerInterface;

/**
 * Synchronize content with assets
 */
class Synchronize implements SynchronizeInterface
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
     * @var SynchronizerPool
     */
    private $synchronizerPool;

    /**
     * @var RemoveObsoleteContentAsset
     */
    private $removeObsoleteContent;

    /**
     * @param RemoveObsoleteContentAsset $removeObsoleteContent
     * @param DateTimeFactory $dateFactory
     * @param FlagManager $flagManager
     * @param LoggerInterface $log
     * @param SynchronizerPool $synchronizerPool
     */
    public function __construct(
        RemoveObsoleteContentAsset $removeObsoleteContent,
        DateTimeFactory $dateFactory,
        FlagManager $flagManager,
        LoggerInterface $log,
        SynchronizerPool $synchronizerPool
    ) {
        $this->removeObsoleteContent = $removeObsoleteContent;
        $this->dateFactory = $dateFactory;
        $this->flagManager = $flagManager;
        $this->log = $log;
        $this->synchronizerPool = $synchronizerPool;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $failed = [];

        foreach ($this->synchronizerPool->get() as $name => $synchronizer) {
            try {
                $synchronizer->execute();
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
