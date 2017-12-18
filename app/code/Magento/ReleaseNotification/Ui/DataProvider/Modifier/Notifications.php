<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Ui\DataProvider\Modifier;

use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\ReleaseNotification\Ui\Renderer\NotificationRenderer;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Ui\Component;

/**
 * Class Notifications
 *
 * Modifies the metadata returning to the Release Notification data provider
 */
class Notifications implements ModifierInterface
{
    /**
     * @var ContentProviderInterface
     */
    private $contentProvider;

    /**
     * @var NotificationRenderer
     */
    private $renderer;

    /**
     * Prefix for cache
     *
     * @var string
     */
    private static $cachePrefix = 'release-notification-content-';

    /**
     * @var CacheInterface
     */
    private $cacheStorage;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ContentProviderInterface $contentProvider
     * @param NotificationRenderer $builder
     * @param CacheInterface $cacheStorage
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ContentProviderInterface $contentProvider,
        NotificationRenderer $builder,
        CacheInterface $cacheStorage,
        SerializerInterface $serializer
    ) {
        $this->contentProvider = $contentProvider;
        $this->renderer = $builder;
        $this->cacheStorage = $cacheStorage;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $modalContent = $this->getNotificationContent();

        if ($modalContent) {
            $pages = $modalContent[0]['pages'];
            $lastPage = end($pages);
            $isLastPage = false;

            foreach ($pages as $page) {
                if ($page == $lastPage) {
                    $isLastPage = true;
                }
                $meta = $this->buildNotificationMeta($meta, $page, $isLastPage);
            }
        } else {
            $meta = $this->buildFallbackNotificationMeta($meta);
        }

        return $meta;
    }

    /**
     * Builds the notification modal by modifying $meta for the ui component
     *
     * @param array $meta
     * @param array $page
     * @param bool $isLastPage
     * @return array
     */
    private function buildNotificationMeta(array $meta, array $page, $isLastPage)
    {
        $meta['notification_modal_' . $page['name']]['arguments']['data']['config'] = [
            'isTemplate' => false,
            'componentType' => Component\Modal::NAME
        ];

        $meta['notification_modal_' . $page['name']]['children']['notification_fieldset']['children']
        ['notification_text']['arguments']['data']['config'] = [
            'text' => $this->renderer->getNotificationContent($page)
        ];

        if ($isLastPage) {
            $meta['notification_modal_' . $page['name']]['arguments']['data']['config']['options'] = [
                'title' => $this->renderer->getNotificationTitle($page),
                'buttons' => [
                    [
                        'text' => 'Done',
                        'actions' => [
                            [
                                'targetName' => '${ $.name }',
                                'actionName' => 'closeReleaseNotes'
                            ]
                        ],
                        'class' => 'release-notification-button-next'
                    ]
                ],
            ];

            $meta['notification_modal_' . $page['name']]['children']['notification_fieldset']['children']
            ['notification_buttons']['children']['notification_button_next']['arguments']['data']['config'] = [
                'buttonClasses' => 'hide-release-notification'
            ];
        } else {
            $meta['notification_modal_' . $page['name']]['arguments']['data']['config']['options'] = [
                'title' => $this->renderer->getNotificationTitle($page)
            ];
        }

        return $meta;
    }

    /**
     * Builds the fallback notification modal in case the Magento Marketing service is unavailable
     *
     * @param array $meta
     * @return array
     */
    private function buildFallbackNotificationMeta(array $meta)
    {
        $meta['notification_modal_1']['arguments']['data']['config']['options'] = [
            'autoOpen' => false
        ];

        return $meta;
    }

    /**
     * Returns the notification modal content data in JSON format from the Magento Marketing service or session cache
     *
     * @returns string
     */
    private function getNotificationContent()
    {
        $cacheKey = self::$cachePrefix . $this->contentProvider->getTargetVersion() . "-"
            . $this->contentProvider->getEdition() . "-" . $this->contentProvider->getLocale();
        $modalContent = $this->cacheStorage->load($cacheKey);
        if ($modalContent === false) {
            $modalContent = $this->contentProvider->getContent();
            $this->cacheStorage->save($modalContent, $cacheKey);
        }

        return $this->serializer->unserialize($modalContent);
    }
}
