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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

class LinkRow extends Block
{
    /**
     * Example: name="downloadable[link][1][price]"
     *
     * @var string
     */
    protected $fieldSelectorTemplate = '[name="downloadable[link][%d][%s]"]';

    /**
     * @param int $rowIndex
     * @param array $rowData
     */
    public function fill($rowIndex, $rowData)
    {
        foreach ([
            'title', 'price', 'number_of_downloads', 'is_unlimited',
            'is_shareable', 'sample][type', 'sample][url', 'type', 'link_url', 'sort_order'
        ] as $field) {
            if (isset($rowData[$field]['value'])) {
                $fieldSelector = sprintf($this->fieldSelectorTemplate, $rowIndex, $field);
                /* @TODO replace with typified radio element */
                $type = isset($rowData[$field]['input']) ? $rowData[$field]['input'] : null;
                if ($type == 'radio') {
                    $type = 'checkbox';
                    $fieldSelector .= sprintf('[value=%s]', $rowData[$field]['value']);
                    $rowData[$field]['value'] = 'Yes';
                }
                $this->_rootElement->find($fieldSelector, Locator::SELECTOR_CSS, $type)
                    ->setValue($rowData[$field]['value']);
            }
        }
    }
}
