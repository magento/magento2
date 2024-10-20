<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Inline;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Translation\Model\FileManager;

/**
 * To manage translations cache
 */
class CacheManager
{
    /**
     * Initialize dependencies
     *
     * @param ManagerInterface $eventManager
     * @param ResourceInterface $translateResource
     * @param ResolverInterface $localeResolver
     * @param FileManager $fileManager
     */
    public function __construct(
        protected readonly ManagerInterface $eventManager,
        protected readonly ResourceInterface $translateResource,
        protected readonly ResolverInterface $localeResolver,
        protected readonly FileManager $fileManager
    ) {
    }

    /**
     * Clear cache and update translations file.
     *
     * @return array
     */
    public function updateAndGetTranslations()
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_system');
        $translations = $this->translateResource->getTranslationArray(null, $this->localeResolver->getLocale());
        $this->fileManager->updateTranslationFileContent($translations);

        return $translations;
    }
}
