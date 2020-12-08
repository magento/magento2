<?php
/**
 * Magento validator config factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\PageLayout\Config;

use Magento\Framework\App\Cache\Type\Layout;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\PageLayout\ConfigFactory;
use Magento\Framework\View\PageLayout\File\Collector\Aggregated;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme\Data;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Page layout config builder
 */
class Builder implements BuilderInterface
{
    const CACHE_KEY_LAYOUTS = 'THEME_LAYOUTS_FILES_MERGED';

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var Aggregated
     */
    protected $fileCollector;

    /**
     * @var Collection
     */
    protected $themeCollection;

    /**
     * @var array
     */
    private $configFiles = [];

    /**
     * @var Layout|null
     */
    private $cacheModel;
    /**
     * @var SerializerInterface|null
     */
    private $serializer;

    /**
     * @param ConfigFactory $configFactory
     * @param Aggregated $fileCollector
     * @param Collection $themeCollection
     * @param Layout|null $cacheModel
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        ConfigFactory $configFactory,
        Aggregated $fileCollector,
        Collection $themeCollection,
        ?Layout $cacheModel = null,
        ?SerializerInterface $serializer = null
    ) {
        $this->configFactory = $configFactory;
        $this->fileCollector = $fileCollector;
        $this->themeCollection = $themeCollection;
        $this->themeCollection->setItemObjectClass(Data::class);
        $this->cacheModel = $cacheModel
            ?? ObjectManager::getInstance()->get(Layout::class);
        $this->serializer = $serializer
            ?? ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getPageLayoutsConfig()
    {
        return $this->configFactory->create(['configFiles' => $this->getConfigFiles()]);
    }

    /**
     * Retrieve configuration files. Caches merged layouts.xml XML files.
     *
     * @return array
     */
    protected function getConfigFiles()
    {
        if (!$this->configFiles) {
            $this->configFiles = $this->cacheModel->load(self::CACHE_KEY_LAYOUTS);
            if (!empty($this->configFiles)) {
                //if value in cache is corrupted.
                $this->configFiles = $this->serializer->unserialize($this->configFiles);
            }
            if (empty($this->configFiles)) {
                $configFiles = [];
                foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                    $configFiles[] = $this->fileCollector->getFilesContent($theme, 'layouts.xml');
                }
                $this->configFiles = array_merge([], ...$configFiles);
                $this->cacheModel->save($this->serializer->serialize($this->configFiles), self::CACHE_KEY_LAYOUTS);
            }
        }

        return $this->configFiles;
    }
}
