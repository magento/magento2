<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache;

/**
 * @api
 * @since 2.0.0
 */
class Additional extends \Magento\Backend\Block\Template
{
    /**
     * Check if application is in production mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function isInProductionMode()
    {
        return $this->_appState->getMode() === \Magento\Framework\App\State::MODE_PRODUCTION;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCleanImagesUrl()
    {
        return $this->getUrl('*/*/cleanImages');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCleanMediaUrl()
    {
        return $this->getUrl('*/*/cleanMedia');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCleanStaticFilesUrl()
    {
        return $this->getUrl('*/*/cleanStaticFiles');
    }
}
