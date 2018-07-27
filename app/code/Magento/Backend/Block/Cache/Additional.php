<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache;

class Additional extends \Magento\Backend\Block\Template
{
    /**
     * Check if application is in production mode
     *
     * @return bool
     */
    public function isInProductionMode()
    {
        return $this->_appState->getMode() === \Magento\Framework\App\State::MODE_PRODUCTION;
    }

    /**
     * @return string
     */
    public function getCleanImagesUrl()
    {
        return $this->getUrl('*/*/cleanImages');
    }

    /**
     * @return string
     */
    public function getCleanMediaUrl()
    {
        return $this->getUrl('*/*/cleanMedia');
    }

    /**
     * @return string
     */
    public function getCleanStaticFilesUrl()
    {
        return $this->getUrl('*/*/cleanStaticFiles');
    }
}
