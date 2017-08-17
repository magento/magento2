<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer\BlockRenderer;

/**
 * Class \Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template
 *
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            (array)$this->getData(),
            [
                'MESSAGE',
                $this->getTemplate(),
                $this->_storeManager->getStore()->getCode()
            ]
        );
    }
}
