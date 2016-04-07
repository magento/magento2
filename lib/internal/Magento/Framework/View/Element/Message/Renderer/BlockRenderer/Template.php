<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer\BlockRenderer;

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
