<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\Js\Config;

class Js extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Translation\Model\FileManager
     */
    private $fileManager;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param \Magento\Translation\Model\FileManager $fileManager
     * @param array $data
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
     */
    public function dictionaryEnabled()
    {
        return $this->config->dictionaryEnabled();
    }

    /**
     * gets current js-translation.json timestamp
     *
     * @return string
     */
    public function getTranslationFileTimestamp()
    {
        return $this->fileManager->getTranslationFileTimestamp();
    }

    /**
     * @return string
     */
    public function getTranslationFilePath()
    {
        return $this->fileManager->getTranslationFilePath();

    }
}
