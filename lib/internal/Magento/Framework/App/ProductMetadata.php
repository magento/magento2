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
     * Get Product version
     *
     * @return string
     */
    public function getVersion()
    {
        if (!$this->version) {
            $composerJsonFile = realpath(BP . DIRECTORY_SEPARATOR . 'composer.json');
            if (!$composerJsonFile || !is_file($composerJsonFile)) {
                return '';
            }
            $composerContent = file_get_contents($composerJsonFile);
            if ($composerContent === false) {
                return '';
            }
            $composerContent = json_decode($composerContent, true);
            if (!$composerContent || !is_array($composerContent) || !array_key_exists('version', $composerContent)) {
                return '';
            }
            $this->version = $composerContent['version'];
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
