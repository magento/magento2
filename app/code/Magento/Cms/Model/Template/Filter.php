<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Template;

/**
 * Cms Template Filter Model
 */
class Filter extends \Magento\Email\Model\Template\Filter
{
    /**
     * Whether to allow SID in store directive: AUTO
     *
     * @var bool
     */
    protected $_useSessionInUrl;

    /**
     * Setter whether SID is allowed in store directive
     *
     * @param bool $flag
     * @return $this
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
     * @throws \InvalidArgumentException
     */
    public function mediaDirective($construction)
    {
        $params = $this->getParameters($construction[2]);
        if (preg_match('/\.\.(\\\|\/)/', $params['url'])) {
            throw new \InvalidArgumentException('Image path must be absolute');
        }

        return $this->_storeManager->getStore()->getBaseMediaDir() . '/' . $params['url'];
    }
}
