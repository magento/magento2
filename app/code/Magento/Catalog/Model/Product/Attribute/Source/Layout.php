<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param LayoutCache|null $layoutCache
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        LayoutCache $layoutCache = null,
        SerializerInterface $serializer = null
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->layoutCache = $layoutCache ?? ObjectManager::getInstance()->get(LayoutCache::class);
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Get list of available layouts
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $layoutCacheKey = __CLASS__;
            if ($data = $this->layoutCache->load($layoutCacheKey)) {
                return $this->_options = $this->serializer->unserialize($data);
            } else {
                $this->_options = $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
                $this->layoutCache->save($this->serializer->serialize($this->_options), $layoutCacheKey);
            }
        }
        array_unshift($this->_options, ['value' => '', 'label' => __('No layout updates')]);
        return $this->_options;
    }
}
