<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Autocomplete queries list
 */
namespace Magento\CatalogSearch\Block;

use Magento\CatalogSearch\Helper\Data;
use Magento\View\Element\AbstractBlock;
use Magento\View\Element\Context;

class Autocomplete extends AbstractBlock
{
    /**
     * @var array
     */
    protected $_suggestData = null;

    /**
     * @var Data
     */
    protected $_catalogsearchHelper;

    /**
     * @param Context $context
     * @param Data $catalogsearchHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $catalogsearchHelper,
        array $data = array()
    ) {
        $this->_catalogsearchHelper = $catalogsearchHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $suggestData = $this->getSuggestData();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $count--;

        $html = '<ul><li style="display:none"></li>';
        foreach ($suggestData as $index => $item) {
            if ($index == 0) {
                $item['row_class'] .= ' first';
            }

            if ($index == $count) {
                $item['row_class'] .= ' last';
            }

            $escapedTitle = $this->escapeHtml($item['title']);
            $html .=  '<li title="'.$escapedTitle.'" class="'.$item['row_class'].'">'
                . '<span class="amount">'.$item['num_of_results'].'</span>'.$escapedTitle.'</li>';
        }

        $html.= '</ul>';

        return $html;
    }

    /**
     * @return array
     */
    public function getSuggestData()
    {
        if (!$this->_suggestData) {
            $collection = $this->_catalogsearchHelper->getSuggestCollection();
            $query = $this->_catalogsearchHelper->getQueryText();
            $counter = 0;
            $data = array();
            foreach ($collection as $item) {
                $_data = array(
                    'title' => $item->getQueryText(),
                    'row_class' => (++$counter)%2?'odd':'even',
                    'num_of_results' => $item->getNumResults()
                );

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                }
                else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }
/*
 *
*/
}
