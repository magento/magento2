<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Template;

/**
 * Cms Template Filter Model
 * @since 2.0.0
 */
class Filter extends \Magento\Email\Model\Template\Filter
{
    /**
     * Whether to allow SID in store directive: AUTO
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_useSessionInUrl;

    /**
     * Setter whether SID is allowed in store directive
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setUseSessionInUrl($flag)
    {
        $this->_useSessionInUrl = (bool)$flag;
        return $this;
    }

    /**
     * Retrieve media file URL directive
     *
     * @param string[] $construction
     * @return string
     * @since 2.0.0
     */
    public function mediaDirective($construction)
    {
        $params = $this->getParameters($construction[2]);
        return $this->_storeManager->getStore()->getBaseMediaDir() . '/' . $params['url'];
    }
}
