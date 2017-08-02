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
 * @since 2.0.0
 */
class Title extends Template
{
    /**
     * Own page title to display on the page
     *
     * @var string
     * @since 2.0.0
     */
    protected $pageTitle;

    /**
     * Provide own page title or pick it from Head Block
     *
     * @return string
     * @since 2.0.0
     */
    public function getPageTitle()
    {
        if (!empty($this->pageTitle)) {
            return $this->pageTitle;
        }
        return __($this->pageConfig->getTitle()->getShort());
    }

    /**
     * Provide own page content heading
     *
     * @return string
     * @since 2.0.0
     */
    public function getPageHeading()
    {
        if (!empty($this->pageTitle)) {
            return __($this->pageTitle);
        }
        return __($this->pageConfig->getTitle()->getShortHeading());
    }

    /**
     * Set own page title
     *
     * @param string $pageTitle
     * @return void
     * @since 2.0.0
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }
}
