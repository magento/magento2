<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model\Queue;

use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Publish media gallery renditions update message to the queue.
 */
class ScheduleRenditionsUpdate
{
    private const TOPIC_MEDIA_GALLERY_UPDATE_RENDITIONS = 'media.gallery.renditions.update';

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Publish media gallery renditions update message to the queue.
     *
     * @param array $paths
     */
    public function execute(array $paths = []): void
    {
        $this->publisher->publish(
            self::TOPIC_MEDIA_GALLERY_UPDATE_RENDITIONS,
            $paths
        );
    }
}
