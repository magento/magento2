<?php
/**
 * Magento application product metadata
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class ProductMetadata implements ProductMetadataInterface
{
    const EDITION_NAME  = 'Community';
    const PRODUCT_NAME  = 'Magento';

    /**
     * Product version
     *
     * @var string
     */
    protected $version;

    /**
     * @var \Magento\Framework\Composer\ComposerJsonFinder
     */
    protected $composerJsonFinder;

    /**
     * @param \Magento\Framework\Composer\ComposerJsonFinder $composerJsonFinder
     */
    public function __construct(\Magento\Framework\Composer\ComposerJsonFinder $composerJsonFinder)
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
            try {
                $composerJsonFile = $this->composerJsonFinder->findComposerJson();

                if (!$composerJsonFile || !is_file($composerJsonFile)) {
                    return '';
                }
                $composerContent = file_get_contents($composerJsonFile);
                if ($composerContent === false) {
                    return '';
                }
                $composerContent = json_decode($composerContent, true);
                if (!$composerContent
                    || !is_array($composerContent)
                    || !array_key_exists('version', $composerContent)
                ) {
                    return '';
                }
                $this->version = $composerContent['version'];
            } catch (\Exception $e) {
                return '';
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
}
