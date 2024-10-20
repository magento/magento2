<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Inline;

use Magento\Framework\Translate\ResourceInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Prepares content of inline translations file.
 */
class File
{
    /**
     * Initialize dependencies
     *
     * @param ResourceInterface $translateResource
     * @param ResolverInterface $localeResolver
     * @param Json $jsonSerializer
     */
    public function __construct(
        private readonly ResourceInterface $translateResource,
        private readonly ResolverInterface $localeResolver,
        private readonly Json $jsonSerializer
    ) {
    }

    /**
     * Generate translation file content for the current locale.
     *
     * @return string
     */
    public function getTranslationFileContent()
    {
        $translations = $this->translateResource->getTranslationArray(null, $this->localeResolver->getLocale());
        $translations = $this->jsonSerializer->serialize($translations);
        return $translations;
    }
}
