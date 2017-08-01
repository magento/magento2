<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Inline;

/**
 * To manage translations cache
 * @since 2.1.0
 */
class CacheManager
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.1.0
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface
     * @since 2.1.0
     */
    protected $translateResource;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.1.0
     */
    protected $localeResolver;

    /**
     * @var \Magento\Translation\Model\FileManager
     * @since 2.1.0
     */
    protected $fileManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Translate\ResourceInterface $translateResource
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Translation\Model\FileManager $fileManager
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Translate\ResourceInterface $translateResource,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Translation\Model\FileManager $fileManager
    ) {
        $this->eventManager = $eventManager;
        $this->translateResource = $translateResource;
        $this->localeResolver = $localeResolver;
        $this->fileManager = $fileManager;
    }

    /**
     * Clears cache and updates translations file
     *
     * @return array
     * @since 2.1.0
     */
    public function updateAndGetTranslations()
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_system');
        $translations = $this->translateResource->getTranslationArray(null, $this->localeResolver->getLocale());
        $this->fileManager->updateTranslationFileContent(json_encode($translations));
        return $translations;
    }
}
