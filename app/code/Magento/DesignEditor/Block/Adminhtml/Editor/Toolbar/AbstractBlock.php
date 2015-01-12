<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract toolbar block
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar;

abstract class AbstractBlock extends \Magento\Backend\Block\Template
{
    /**
     * Current VDE mode
     *
     * @var int
     */
    protected $_mode;

    /**
     * Get current VDE mode
     *
     * @return int
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Get current VDE mode
     *
     * @param int $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;

        return $this;
    }

    /**
     * Check if visual editor is in navigation mode
     *
     * @return bool
     */
    public function isNavigationMode()
    {
        return $this->getMode() == \Magento\DesignEditor\Model\State::MODE_NAVIGATION;
    }
}
