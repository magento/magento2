<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Theme;

/**
 * Design editor theme change
 */
class Change extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Theme model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\DesignEditor\Model\Theme\Resource\Change');
    }

    /**
     * Load alias for theme id
     *
     * @param int $themeId
     * @return $this
     */
    public function loadByThemeId($themeId)
    {
        $this->load($themeId, 'theme_id');
        return $this;
    }
}
