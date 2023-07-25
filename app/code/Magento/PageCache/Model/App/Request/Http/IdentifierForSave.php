<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\PageCache\IdentifierInterface;

/**
 * Page unique identifier
 */
class IdentifierForSave implements IdentifierInterface
{
    /**
     * @param Http $request
     * @param Context $context
     * @param Json $serializer
     */
    public function __construct(
        private Http $request,
        private Context $context,
        private Json $serializer
    ) {
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->context->getVaryString()
        ];

        return sha1($this->serializer->serialize($data));
    }
}
