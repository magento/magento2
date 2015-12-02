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
     * @throws \Exception
     */
    public function getVersion()
    {
        if (!$this->version) {
            $composerJsonFile = $this->composerJsonFinder->findComposerJson();

            $composerContent = file_get_contents($composerJsonFile);
            if ($composerContent === false) {
                throw new \Exception('Composer file content is empty');
            }
            $composerContent = json_decode($composerContent, true);
            if (!$composerContent
                || !is_array($composerContent)
                || !array_key_exists('version', $composerContent)
            ) {
                throw new \Exception('Unable to decode Composer file');
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
