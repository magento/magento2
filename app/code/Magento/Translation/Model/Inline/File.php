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
     * @var ResourceInterface
     */
    private $translateResource;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Initialize dependencies
     *
     * @param ResourceInterface $translateResource
     * @param ResolverInterface $localeResolver
     * @param Json $jsonSerializer
     */
    public function __construct(
        ResourceInterface $translateResource,
        ResolverInterface $localeResolver,
        Json $jsonSerializer
    ) {
        $this->translateResource = $translateResource;
        $this->localeResolver = $localeResolver;
        $this->jsonSerializer = $jsonSerializer;
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
