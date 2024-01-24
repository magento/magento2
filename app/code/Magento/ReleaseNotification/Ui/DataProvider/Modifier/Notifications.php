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
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Psr\Log\LoggerInterface;

/**
 * Modifies the metadata returning to the Release Notification data provider
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @deprecated Starting from Magento OS 2.4.7 Magento_ReleaseNotification module is deprecated
 * in favor of another in-product messaging mechanism
 * @see Current in-product messaging mechanism
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
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContentProviderInterface $contentProvider
     * @param NotificationRenderer $render
     * @param CacheInterface $cacheStorage
     * @param SerializerInterface $serializer
     * @param ProductMetadataInterface $productMetadata
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContentProviderInterface $contentProvider,
        NotificationRenderer $render,
        CacheInterface $cacheStorage,
        SerializerInterface $serializer,
        ProductMetadataInterface $productMetadata,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->contentProvider = $contentProvider;
        $this->renderer = $render;
        $this->cacheStorage = $cacheStorage;
        $this->serializer = $serializer;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $modalContent = $this->getNotificationContent();

        if ($modalContent) {
            $pages = $modalContent['pages'];
            $pageCount = count($pages);
            $counter = 1;

            foreach ($pages as $page) {
                $meta = $this->buildNotificationMeta($meta, $page, $counter++ == $pageCount);
            }
        } else {
            $meta = $this->hideNotification($meta);
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
                                '__disableTmpl' => ['targetName' => false],
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
     * Sets the modal to not display if no content is available.
     *
     * @param array $meta
     * @return array
     */
    private function hideNotification(array $meta)
    {
        $meta['notification_modal_1']['arguments']['data']['config']['options'] = [
            'autoOpen' => false
        ];

        return $meta;
    }

    /**
     * Returns the notification modal content data
     *
     * @returns array|false
     */
    private function getNotificationContent()
    {
        $version = strtolower($this->getTargetVersion());
        $edition = strtolower($this->productMetadata->getEdition());
        $locale = $this->session->getUser()->getInterfaceLocale();
        $locale = $locale !== null ? strtolower($locale) : '';

        $cacheKey = self::$cachePrefix . $version . "-" . $edition . "-" . $locale;
        $modalContent = $this->cacheStorage->load($cacheKey);
        if ($modalContent === false) {
            $modalContent = $this->contentProvider->getContent($version, $edition, $locale);
            $this->cacheStorage->save($modalContent, $cacheKey);
        }

        return !$modalContent ? $modalContent : $this->unserializeContent($modalContent);
    }

    /**
     * Unserializes the notification modal content to be used for rendering
     *
     * @param string $modalContent
     * @return array|false
     */
    private function unserializeContent($modalContent)
    {
        $result = false;

        try {
            $result = $this->serializer->unserialize($modalContent);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning(
                sprintf(
                    'Failed to unserialize the release notification content. The error is: %s',
                    $e->getMessage()
                )
            );
        }

        return $result;
    }

    /**
     * Returns the current Magento version used to retrieve the release notification content.
     *
     * Version information after the dash (-) character is removed (ex. -dev or -rc).
     *
     * @return string
     */
    private function getTargetVersion()
    {
        $metadataVersion = $this->productMetadata->getVersion();
        $version = strstr($metadataVersion, '-', true);

        return !$version ? $metadataVersion : $version;
    }
}
