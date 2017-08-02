<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Js;

/**
 * Js Translation config
 * @since 2.0.0
 */
class Config
{
    /**
     * Should the framework generate dictionary file
     *
     * @var bool
     * @since 2.0.0
     */
    protected $dictionaryEnabled;

    /**
     * Name of dictionary json file
     *
     * @var string
     * @since 2.0.0
     */
    protected $dictionaryFileName;

    /**
     * @param bool $dictionaryEnabled
     * @param string $dictionaryFileName
     * @since 2.0.0
     */
    public function __construct($dictionaryEnabled = false, $dictionaryFileName = null)
    {
        $this->dictionaryEnabled = $dictionaryEnabled;
        $this->dictionaryFileName = $dictionaryFileName;
    }

    /**
     * Should the framework generate dictionary file
     *
     * @return bool
     * @since 2.0.0
     */
    public function dictionaryEnabled()
    {
        return $this->dictionaryEnabled;
    }

    /**
     * Name of dictionary json file
     *
     * @return string
     * @since 2.0.0
     */
    public function getDictionaryFileName()
    {
        return $this->dictionaryFileName;
    }
}
