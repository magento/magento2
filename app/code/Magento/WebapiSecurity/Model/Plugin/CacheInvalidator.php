<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiSecurity\Model\Plugin;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Value;
use Magento\Webapi\Model\Cache\Type\Webapi;

class CacheInvalidator
{
    /**
     * CacheInvalidator constructor.
     *
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        protected readonly TypeListInterface $cacheTypeList
    ) {
    }

    /**
     * Invalidate WebApi cache if needed.
     *
     * @param Value $subject
     * @param Value $result
     * @return Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        Value $subject,
        Value $result
    ) {
        if ($result->getPath() == AnonymousResourceSecurity::XML_ALLOW_INSECURE
            && $result->isValueChanged()
        ) {
            $this->cacheTypeList->invalidate(Webapi::TYPE_IDENTIFIER);
        }

        return $result;
    }
}
