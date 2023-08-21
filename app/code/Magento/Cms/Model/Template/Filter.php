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
     * Retrieve media file URL directive
     *
     * @param string[] $construction
     * @return string
     */
    public function mediaDirective($construction)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $params = $this->getParameters(html_entity_decode($construction[2], ENT_QUOTES));
        if (preg_match('/(^.*:\/\/.*|\.\.\/.*)/', $params['url'])) {
            throw new \InvalidArgumentException('Image path must be absolute and not include URLs');
        }

        return $this->_storeManager->getStore()->getBaseMediaDir() . '/' . $params['url'];
    }
}
