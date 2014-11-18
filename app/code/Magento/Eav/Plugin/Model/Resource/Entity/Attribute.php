<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Plugin\Model\Resource\Entity;

class Attribute
{
    /**
     * Cache key for store label attribute
     */
    const STORE_LABEL_ATTRIBUTE = 'EAV_STORE_LABEL_ATTRIBUTE';

    /** @var \Magento\Framework\App\CacheInterface */
    protected $cache;

    /** @var bool|null */
    protected $isCacheEnabled = null;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->cache = $cache;
        $this->isCacheEnabled = $cacheState->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
    }

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute $subject
     * @param callable $proceed
     * @param int $attributeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStoreLabelsByAttributeId(
        \Magento\Eav\Model\Resource\Entity\Attribute $subject,
        \Closure $proceed,
        $attributeId
    ) {
        $cacheId = self::STORE_LABEL_ATTRIBUTE . $attributeId;
        if ($this->isCacheEnabled && ($storeLabels = $this->cache->load($cacheId))) {
            return unserialize($storeLabels);
        }
        $storeLabels = $proceed($attributeId);
        if ($this->isCacheEnabled) {
            $this->cache->save(
                serialize($storeLabels),
                $cacheId,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }
        return $storeLabels;
    }
}
