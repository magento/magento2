<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\Varnish;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\PageCache\Model\VclTemplateLocatorInterface;
use Magento\PageCache\Exception\UnsupportedVarnishVersion;

/**
 * Class \Magento\PageCache\Model\Varnish\VclTemplateLocator
 *
 * @since 2.2.0
 */
class VclTemplateLocator implements VclTemplateLocatorInterface
{
    /**
     * XML path to Varnish 5 config template path
     */
    const VARNISH_5_CONFIGURATION_PATH = 'system/full_page_cache/varnish5/path';

    /**
     * XML path to Varnish 4 config template path
     */
    const VARNISH_4_CONFIGURATION_PATH = 'system/full_page_cache/varnish4/path';

    /**
     *
     */
    const VARNISH_SUPPORTED_VERSION_4 = '4';

    /**
     *
     */
    const VARNISH_SUPPORTED_VERSION_5 = '5';

    /**
     * @var array
     * @since 2.2.0
     */
    private $supportedVarnishVersions = [
        self::VARNISH_SUPPORTED_VERSION_4 => self::VARNISH_4_CONFIGURATION_PATH,
        self::VARNISH_SUPPORTED_VERSION_5 => self::VARNISH_5_CONFIGURATION_PATH,
    ];

    /**
     * @var Reader
     * @since 2.2.0
     */
    private $reader;

    /**
     * @var ReadFactory
     * @since 2.2.0
     */
    private $readFactory;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * VclTemplateLocator constructor.
     *
     * @param Reader $reader
     * @param ReadFactory $readFactory
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(Reader $reader, ReadFactory $readFactory, ScopeConfigInterface $scopeConfig)
    {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getTemplate($version)
    {
        $moduleEtcPath = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_PageCache');
        $configFilePath = $moduleEtcPath . '/' . $this->scopeConfig->getValue($this->getVclTemplatePath($version));
        $directoryRead = $this->readFactory->create($moduleEtcPath);
        $configFilePath = $directoryRead->getRelativePath($configFilePath);
        $template = $directoryRead->readFile($configFilePath);
        return $template;
    }

    /**
     * Get Vcl template path
     *
     * @param int $version Varnish version
     * @return string
     * @throws UnsupportedVarnishVersion
     * @since 2.2.0
     */
    private function getVclTemplatePath($version)
    {
        if (!isset($this->supportedVarnishVersions[$version])) {
            throw new UnsupportedVarnishVersion(__('Unsupported varnish version'));
        }

        return $this->supportedVarnishVersions[$version];
    }
}
