<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\WebapiSecurity\Model\Plugin;

class CacheInvalidator
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * CacheInvalidator constructor.
     *
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
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
