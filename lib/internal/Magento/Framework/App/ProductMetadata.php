<?php
/**
 * Magento application product metadata
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\Composer\ComposerFactory;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerInformation;

/**
 * Magento application product metadata
 */
class ProductMetadata implements ProductMetadataInterface
{
    /**
     * Magento product edition
     */
    const EDITION_NAME  = 'Community';

    /**
     * Magento product name
     */
    const PRODUCT_NAME  = 'Magento';

    /**
     * Magento version cache key
     */
    const VERSION_CACHE_KEY = 'mage-version';

    /**
     * Product version
     *
     * @var string
     */
    protected $version;

    /**
     * @var \Magento\Framework\Composer\ComposerJsonFinder
     * @deprecated 100.1.0
     */
    protected $composerJsonFinder;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * ProductMetadata constructor.
     * @param ComposerJsonFinder $composerJsonFinder
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        ComposerJsonFinder $composerJsonFinder,
        ?CacheInterface $cache = null
    ) {
        $this->composerJsonFinder = $composerJsonFinder;
        $this->cache = $cache ?: ObjectManager::getInstance()->get(CacheInterface::class);
    }

    /**
     * Get Product version
     *
     * @return string
     */
    public function getVersion()
    {
        $this->version = $this->version ?: $this->cache->load(self::VERSION_CACHE_KEY);
        if (!$this->version) {
            if (!($this->version = $this->getSystemPackageVersion())) {
                if ($this->getComposerInformation()->isMagentoRoot()) {
                    $this->version = $this->getComposerInformation()->getRootPackage()->getPrettyVersion();
                } else {
                    $this->version = 'UNKNOWN';
                }
            }
            $this->cache->save($this->version, self::VERSION_CACHE_KEY, [Config::CACHE_TAG]);
        }
        return $this->version;
    }

    /**
     * Get Product edition
     *
     * @return string
     */
    public function getEdition()
    {
        return self::EDITION_NAME;
    }

    /**
     * Get Product name
     *
     * @return string
     */
    public function getName()
    {
        return self::PRODUCT_NAME;
    }

    /**
     * Get version from system package
     *
     * @return string
     * @deprecated 100.1.0
     */
    private function getSystemPackageVersion()
    {
        $packages = $this->getComposerInformation()->getSystemPackages();
        foreach ($packages as $package) {
            if (isset($package['name']) && isset($package['version'])) {
                return $package['version'];
            }
        }
        return '';
    }

    /**
     * Load composerInformation
     *
     * @return ComposerInformation
     * @deprecated 100.1.0
     */
    private function getComposerInformation()
    {
        if (!$this->composerInformation) {
            $directoryList              = new DirectoryList(BP);
            $composerFactory            = new ComposerFactory($directoryList, $this->composerJsonFinder);
            $this->composerInformation  = new ComposerInformation($composerFactory);
        }
        return $this->composerInformation;
    }
}
