<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $this->_rendererTypes['options'] = 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Extended';
        $this->_filterTypes['options'] = 'Magento\Backend\Block\Widget\Grid\Column\Filter\Select\Extended';
        $this->_rendererTypes['select'] = 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Select\Extended';
        $this->_rendererTypes['checkbox'] = 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes\Extended';
        $this->_rendererTypes['radio'] = 'Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio\Extended';

        parent::__construct($context, $data);
    }
}
