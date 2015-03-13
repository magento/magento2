<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Webdev;

use Magento\Tools\View\Deployer\Log;

/**
 * Class CliParams
 *
 * @package Magento\Tools\Webdev
 */
class CliParams
{
    /**
     * AREA_DOC
     */
    const AREA_DOC = 'doc';

    /**
     * AREA_FRONTEND
     */
    const AREA_FRONTEND = 'frontend';

    /**
     * AREA_ADMIN
     */
    const AREA_ADMIN = 'adminhtml';

    /**
     * @var string
     */
    private $locale = 'en_US';

    /**
     * @var string
     */
    private $area = self::AREA_FRONTEND;

    /**
     * @var string
     */
    private $theme = 'Magento/blank';

    /**
     * @var array
     */
    private $files = ['css/styles-m'];

    /**
     * @var string
     */
    private $ext;

    /**
     * @var int
     */
    private $verbose = Log::ERROR;

    /**
     * @param \Zend_Console_Getopt $opt
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Zend_Console_Getopt_Exception
     */
    public function __construct(\Zend_Console_Getopt $opt)
    {
        $this->locale = $opt->getOption('locale')? :$this->locale;

        if (!$opt->getOption('ext')) {
            throw new \Zend_Console_Getopt_Exception('Provide "ext" parameter!');
        }

        $this->ext = $opt->getOption('ext');

        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $this->locale)) {
            throw new \Zend_Console_Getopt_Exception('Invalid locale format');
        }

        $this->area = $opt->getOption('area')? :$this->area;
        $this->theme = $opt->getOption('theme')? :$this->theme;

        if ($opt->getOption('files')) {
            $this->files = explode(',', $opt->getOption('files'));
        }

        if ($opt->getOption('verbose')) {
            $this->verbose = Log::ERROR | Log::DEBUG;
        }
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @throws \Zend_Console_Getopt_Exception
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param string $area
     *
     * @return void
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     *
     * @return void
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $files
     *
     * @return void
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return int
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param int $verbose
     *
     * @return void
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }
}
