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
namespace Magento\Email\Block\Adminhtml\Template\Grid\Filter;

/**
 * Adminhtml system template grid type filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Email template types
     *
     * @var array
     */
    protected static $_types = array(
        null => null,
        \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML => 'HTML',
        \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT => 'Text'
    );

    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions()
    {
        $result = array();
        foreach (self::$_types as $code => $label) {
            $result[] = array('value' => $code, 'label' => __($label));
        }

        return $result;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }

        return array('eq' => $this->getValue());
    }
}
