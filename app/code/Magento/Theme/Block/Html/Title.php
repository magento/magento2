<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Framework\View\Element\Template;

/**
 * Html page title block
 *
 * @method $this setTitleId($titleId)
 * @method $this setTitleClass($titleClass)
 * @method string getTitleId()
 * @method string getTitleClass()
 * @api
 * @since 100.0.2
 */
class Title extends Template
{
    /**
     * Own page title to display on the page
     *
     * @var string
     */
    protected $pageTitle;

    /**
     * Defines whether the i18n translation should be applied for title
     *
     * @var bool
     */
    protected $useTranslation = true;

    /**
     * Provide own page title or pick it from Head Block
     *
     * @return string
     */
    public function getPageTitle()
    {
        if (!empty($this->pageTitle)) {
            return $this->useTranslation ? __($this->pageTitle) : $this->pageTitle;
        }

        $shortTitle = $this->pageConfig->getTitle()->getShort();
        return $this->useTranslation ? __($shortTitle) : $shortTitle;
    }

    /**
     * Provide own page content heading
     *
     * @return string
     */
    public function getPageHeading()
    {
        if (!empty($this->pageTitle)) {
            return $this->useTranslation ? __($this->pageTitle) : $this->pageTitle;
        }

        $shortHeading = $this->pageConfig->getTitle()->getShortHeading();
        return $this->useTranslation ? __($shortHeading) : $shortHeading;
    }

    /**
     * Set own page title
     *
     * @param string $pageTitle
     * @return void
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * Define whether the i18n translation should be applied for title
     * E.g. the additional translations should be disabled for the category title specified on store view level
     *
     * @param bool $useTranslation
     * @return void
     */
    public function setUseTranslations($useTranslation)
    {
        $this->useTranslation = $useTranslation;
    }
}
