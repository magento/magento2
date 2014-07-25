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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super;

use Mtf\Client\Element;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options as CatalogOptions;

/**
 * Class Matrix
 * Matrix row form
 */
class Matrix extends CatalogOptions
{
    /**
     * CSS selector data cell
     *
     * @var string
     */
    protected $cellSelector = 'td:nth-child(%d)';

    /**
     * Field name mapping
     *
     * @var array
     */
    protected $fieldNameMapping = [
        3 => 'name',
        4 => 'sku',
        5 => 'price',
        6 => 'qty',
        7 => 'weight'
    ];

    /**
     * Getting product matrix data form on the product form
     *
     * @param array|null $fields [optional]
     * @param Element|null $element [optional]
     * @return array
     */
    public function getDataOptions(array $fields = null, Element $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);
        $data = $this->_getData($mapping, $element);

        $column = 3;
        $cell = $element->find(sprintf($this->cellSelector, $column));
        $data['options_names'] = [];
        while ($cell->isVisible()) {
            if (isset($this->fieldNameMapping[$column])) {
                $data[$this->fieldNameMapping[$column]] = $cell->getText();
            } else {
                $data['options_names'][] = $cell->getText();
            }
            $cell = $element->find(sprintf($this->cellSelector, ++$column));
        }

        return $data;
    }
}
