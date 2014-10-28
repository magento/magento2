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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class AdvancedPropertiesTab
 * Tab "Advanced Attribute Properties"
 */
class Advanced extends Tab
{
    /**
     * "Advanced Attribute Properties" tab-button
     *
     * @var string
     */
    protected $propertiesTab = '[data-target="#advanced_fieldset-content"][data-toggle="collapse"]';

    /**
     * "Advanced Attribute Properties" tab-button active
     *
     * @var string
     */
    protected $propertiesTabActive = '.title.active';

    /**
     * Fill 'Advanced Attribute Properties' tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!$this->_rootElement->find($this->propertiesTabActive)->isVisible()) {
            $this->_rootElement->find($this->propertiesTab)->click();
        }

        return parent::fillFormTab($fields);
    }
}
