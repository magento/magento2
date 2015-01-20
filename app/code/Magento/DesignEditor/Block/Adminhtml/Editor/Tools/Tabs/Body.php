<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block that renders Design tab
 *
 * @method string getContentBlock()
 * @method string getTabId()
 * @method bool getIsActive()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs\Body setContentBlock($content)
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs\Body setIsActive($flag)
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs\Body setTabId($id)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs;

class Body extends \Magento\Framework\View\Element\Template
{
    /**
     * Get tab content
     *
     * @return string
     */
    public function getContent()
    {
        $content = '';
        $alias = $this->getContentBlock();
        if ($alias) {
            $content = $this->getParentBlock()->getChildHtml($alias);
        }

        return $content;
    }
}
