<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 2.0.0
 */
class GlobalSearch extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::system/search.phtml';

    /**
     * Get components configuration
     * @return array
     * @since 2.0.0
     */
    public function getWidgetInitOptions()
    {
        return [
            'suggest' => [
                'dropdownWrapper' => '<div class="autocomplete-results" ></div >',
                'template' => '[data-template=search-suggest]',
                'termAjaxArgument' => 'query',
                'source' => $this->getUrl('adminhtml/index/globalSearch'),
                'filterProperty' => 'name',
                'preventClickPropagation' => false,
                'minLength' => 2,
            ]
        ];
    }
}
