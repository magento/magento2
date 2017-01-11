<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Js;

/**
 * Js Translation config
 */
class Config
{
    /**
     * Should the framework generate dictionary file
     *
     * @var bool
     */
    protected $dictionaryEnabled;

    /**
     * Name of dictionary json file
     *
     * @var string
     */
    protected $dictionaryFileName;

    /**
     * @param bool $dictionaryEnabled
     * @param string $dictionaryFileName
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
     */
    public function dictionaryEnabled()
    {
        return $this->dictionaryEnabled;
    }

    /**
     * Name of dictionary json file
     *
     * @return string
     */
    public function getDictionaryFileName()
    {
        return $this->dictionaryFileName;
    }
}
