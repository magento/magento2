<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config\Structure;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\Framework\View\Page\Config\Generator\Head
 *
 */
class Head implements Layout\GeneratorInterface
{
    /**#@+
     * Available src_type in assets
     */
    const SRC_TYPE_RESOURCE = 'resource';
    const SRC_TYPE_CONTROLLER = 'controller';
    const SRC_TYPE_URL = 'url';
    /**#@-*/

    /**
     * Type of generator
     */
    const TYPE = 'head';

    /**
     * Virtual content type
     */
    const VIRTUAL_CONTENT_TYPE_LINK = 'link';

    /**
     * @var array
     */
    protected $remoteAssetTypes = [
        self::SRC_TYPE_CONTROLLER => self::SRC_TYPE_CONTROLLER,
        self::SRC_TYPE_URL => self::SRC_TYPE_URL,
    ];

    /**
     * @var array
     */
    protected $assetProperties = [
        'ie_condition',
    ];

    /**
     * @var array
     */
    protected $serviceAssetProperties = [
        'src',
        'src_type',
        'content_type',
    ];

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\UrlInterface|null $url
     */
    public function __construct(
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\UrlInterface $url = null
    ) {
        $this->pageConfig = $pageConfig;
        $this->url = $url ?: ObjectManager::getInstance()->get(\Magento\Framework\UrlInterface::class);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Generator\Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $structure = $readerContext->getPageConfigStructure();
        $structure->processRemoveAssets();
        $structure->processRemoveElementAttributes();

        $this->processAssets($structure);
        $this->processTitle($structure);
        $this->processMetadata($structure);
        $this->processElementAttributes($structure);
        return $this;
    }

    /**
     * Add assets to page config
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processAssets(Structure $pageStructure)
    {
        foreach ($pageStructure->getAssets() as $name => $data) {
            if (isset($data['src_type']) && in_array($data['src_type'], $this->remoteAssetTypes)) {
                if ($data['src_type'] === self::SRC_TYPE_CONTROLLER) {
                    $data['src'] = $this->url->getUrl($data['src']);
                }

                $this->pageConfig->addRemotePageAsset(
                    $data['src'],
                    isset($data['content_type']) ? $data['content_type'] : self::VIRTUAL_CONTENT_TYPE_LINK,
                    $this->getAssetProperties($data),
                    $name
                );
            } else {
                $this->pageConfig->addPageAsset($name, $this->getAssetProperties($data));
            }
        }
        return $this;
    }

    /**
     * Process asset properties
     *
     * @param array $data
     * @return array
     */
    protected function getAssetProperties(array $data = [])
    {
        $properties = [];
        $attributes = [];
        foreach ($data as $name => $value) {
            if (in_array($name, $this->assetProperties)) {
                $properties[$name] = $value;
            } elseif (!in_array($name, $this->serviceAssetProperties)) {
                $attributes[$name] = $value;
            }
        }
        $properties['attributes'] = $attributes;
        return $properties;
    }

    /**
     * Process title
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processTitle(Structure $pageStructure)
    {
        if ($pageStructure->getTitle()) {
            $this->pageConfig->getTitle()->set($pageStructure->getTitle());
        }
        return $this;
    }

    /**
     * Process metadata
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processMetadata(Structure $pageStructure)
    {
        foreach ($pageStructure->getMetadata() as $name => $content) {
            $this->pageConfig->setMetadata($name, $content);
        }
        return $this;
    }

    /**
     * Process all element attributes
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processElementAttributes(Structure $pageStructure)
    {
        foreach ($pageStructure->getElementAttributes() as $element => $attributes) {
            foreach ($attributes as $name => $value) {
                $this->pageConfig->setElementAttribute($element, $name, $value);
            }
        }
        return $this;
    }
}
