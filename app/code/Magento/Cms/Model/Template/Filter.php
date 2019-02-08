<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Template;

use Magento\Framework\Exception\LocalizedException;

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
     */
    public function mediaDirective($construction)
    {
        $params = $this->getParameters(html_entity_decode($construction[2], ENT_QUOTES));
        return $this->_storeManager->getStore()->getBaseMediaDir() . '/' . $params['url'];
    }

    /**
     * Validates directive param for traversal path
     *
     * @param string $directive
     * @return string
     */
    public function filter($directive)
    {
        if (preg_match('#\.\.[\\\/]#', $directive)) {
            throw new LocalizedException(
                __(
                    'Requested file should not include parent directory traversal ("../", "..\\" notation)'
                )
            );
        }

        return parent::filter($directive);
    }
}
