<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\Js\Config;

/**
 * @api
 * @since 2.0.0
 */
class Js extends Template
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Translation\Model\FileManager
     * @since 2.0.0
     */
    private $fileManager;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param \Magento\Translation\Model\FileManager $fileManager
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        \Magento\Translation\Model\FileManager $fileManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->fileManager = $fileManager;
    }

    /**
     * Is js translation set to dictionary mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function dictionaryEnabled()
    {
        return $this->config->dictionaryEnabled();
    }

    /**
     * gets current js-translation.json timestamp
     *
     * @return string
     * @since 2.0.0
     */
    public function getTranslationFileTimestamp()
    {
        return $this->fileManager->getTranslationFileTimestamp();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTranslationFilePath()
    {
        return $this->fileManager->getTranslationFilePath();
    }
}
