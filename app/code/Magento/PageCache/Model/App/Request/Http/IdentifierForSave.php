<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Framework\App\Http\Context;
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
     */
    public function __construct(
        private Http                  $request,
        private Context               $context,
        private Json                  $serializer,
        private IdentifierStoreReader $identifierStoreReader,
    ) {
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $pattern = Identifier::PATTERN_MARKETING_PARAMETERS;
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
