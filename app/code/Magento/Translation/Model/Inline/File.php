<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Inline;

/**
 * Prepares content of inline translations file.
 */
class File
{
    /**
     * @var \Magento\Framework\Translate\ResourceInterface
     */
    private $translateResource;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Translate\ResourceInterface $translateResource
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\Translate\ResourceInterface $translateResource,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
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
