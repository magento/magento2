<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\PageCache\IdentifierInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class IdentifierForSave implements IdentifierInterface
{
    /**
     * @param Http $request
     * @param Context $context
     * @param Json $serializer
     * @param IdentifierStoreReader $identifierStoreReader
     * @param Identifier|null $identifier
     */
    public function __construct(
        private Http                  $request,
        private Context               $context,
        private Json                  $serializer,
        private IdentifierStoreReader $identifierStoreReader,
        private ?Identifier $identifier = null
    ) {
        $this->identifier = $identifier ?: ObjectManager::getInstance()
            ->get(Identifier::class);
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $pattern = $this->identifier->getMarketingParameterPatterns();
        $replace = array_fill(0, count($pattern), '');
        $data = [
            $this->request->isSecure(),
            preg_replace($pattern, $replace, (string)$this->request->getUriString()),
            $this->context->getVaryString()
        ];

        $data = $this->identifierStoreReader->getPageTagsWithStoreCacheTags($data);

        return sha1($this->serializer->serialize($data));
    }
}
