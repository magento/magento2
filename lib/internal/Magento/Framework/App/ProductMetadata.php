<?php
/**
 * Magento application product metadata
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\Composer\ComposerFactory;
use \Magento\Framework\Composer\ComposerJsonFinder;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Composer\ComposerInformation;

/**
 * Class ProductMetadata
 * @package Magento\Framework\App
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
     * Product version
     *
     * @var string
     */
    protected $version;

    /**
     * @var \Magento\Framework\Composer\ComposerJsonFinder
     * @deprecated
     */
    protected $composerJsonFinder;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @param ComposerJsonFinder $composerJsonFinder
     */
    public function __construct(ComposerJsonFinder $composerJsonFinder)
    {
        $this->composerJsonFinder = $composerJsonFinder;
    }

    /**
     * Get Product version
     *
     * @return string
     */
    public function getVersion()
    {
        if (!$this->version) {
            if (!($this->version = $this->getSystemPackageVersion())) {
                if ($this->getComposerInformation()->isMagentoRoot()) {
                    $this->version = $this->getComposerInformation()->getRootPackage()->getPrettyVersion();
                } else {
                    $this->version = 'UNKNOWN';
                }
            }
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
     * @deprecated
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
     * @deprecated
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
