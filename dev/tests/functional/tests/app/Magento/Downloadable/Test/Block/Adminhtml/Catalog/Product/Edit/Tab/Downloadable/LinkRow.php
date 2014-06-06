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

use Mtf\Block\Form;

/**
 * Class LinkRow
 *
 * Form item links
 */
class LinkRow extends Form
{
    /**
     * Fill item link
     *
     * @param array $fields
     * @return void
     */
    public function fillLinkRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Get data item link
     *
     * @param array $fields
     * @return array
     */
    public function getDataLinkRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping);
    }
}
