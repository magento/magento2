<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\Resource\Attribute;

class Save
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $typeList
    ) {
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Attribute $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $attribute
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\Resource\Attribute $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $attribute
    ) {
        $result = $proceed($attribute);
        if ($this->config->isEnabled()) {
            $this->typeList->invalidate('full_page');
        }
        return $result;
    }
}
