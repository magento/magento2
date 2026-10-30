<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Plugin;

use Magento\MediaContentSynchronization\Model\Publish;
use Magento\MediaGallerySynchronization\Model\Consume;

/**
 * Run media content synchronization after the media files consumer finish files synchronization.
 */
class SynchronizeMediaContent
{
    /**
     * @var Publish
     */
    private $publish;

    /**
     * @param Publish $publish
     */
    public function __construct(Publish $publish)
    {
        $this->publish = $publish;
    }

    /**
     * Publish content synchronization request message to the queue.
     *
     * @param Consume $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Consume $subject): void
    {
        $this->publish->execute();
    }
}
