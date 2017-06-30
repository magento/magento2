<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Inline;

/**
 * To manage translations cache
 */
class CacheManager
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface
     */
    protected $translateResource;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Translation\Model\FileManager
     */
    protected $fileManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Translate\ResourceInterface $translateResource
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Translation\Model\FileManager $fileManager
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
     */
    public function updateAndGetTranslations()
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_system');
        $translations = $this->translateResource->getTranslationArray(null, $this->localeResolver->getLocale());
        $this->fileManager->updateTranslationFileContent(json_encode($translations));
        return $translations;
    }
}
