<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block;

class GlobalSearch extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/search.phtml';

    /**
     * Get components configuration
     * @return array
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
