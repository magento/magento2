<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiSecurity\Model\Plugin;

/**
 * Class \Magento\WebapiSecurity\Model\Plugin\CacheInvalidator
 *
 * @since 2.1.0
 */
class CacheInvalidator
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     * @since 2.1.0
     */
    protected $cacheTypeList;

    /**
     * CacheInvalidator constructor.
     *
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Invalidate WebApi cache if needed.
     *
     * @param \Magento\Framework\App\Config\Value $subject
     * @param \Magento\Framework\App\Config\Value $result
     * @return \Magento\Framework\App\Config\Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterAfterSave(
        \Magento\Framework\App\Config\Value $subject,
        \Magento\Framework\App\Config\Value $result
    ) {
        if ($result->getPath() == \Magento\WebapiSecurity\Model\Plugin\AnonymousResourceSecurity::XML_ALLOW_INSECURE
            && $result->isValueChanged()
        ) {
            $this->cacheTypeList->invalidate(\Magento\Webapi\Model\Cache\Type\Webapi::TYPE_IDENTIFIER);
        }

        return $result;
    }
}
