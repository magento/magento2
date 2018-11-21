<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\ObjectManager;

/**
 * Catalog product landing page attribute source
 */
class Layout extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @var LayoutCache
     */
    private $layoutCache;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param LayoutCache|null $layoutCache
     */
    public function __construct(
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        LayoutCache $layoutCache = null
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->layoutCache = $layoutCache ?? ObjectManager::getInstance()->get(LayoutCache::class);
    }

    /**
     * Get list of available layouts
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $layoutCacheKey = __CLASS__;
            if ($data = $this->layoutCache->load($layoutCacheKey)) {
                return $this->_options = unserialize($data);
            } else {
                $this->_options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
                array_unshift($this->_options, ['value' => '', 'label' => __('No layout updates')]);
                $this->layoutCache->save(serialize($this->_options), $layoutCacheKey);
            }

        }
        return $this->_options;
    }
}
