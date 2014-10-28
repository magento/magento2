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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer;

/**
 * Renderer for Qty field in sales create new order search grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * Type config
     *
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $typeConfig;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $typeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $typeConfig,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->typeConfig = $typeConfig;
    }

    /**
     * Returns whether this qty field must be inactive
     *
     * @param \Magento\Framework\Object $row
     * @return bool
     */
    protected function _isInactive($row)
    {
        return $this->typeConfig->isProductSet($row->getTypeId());
    }

    /**
     * Render product qty field
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        // Prepare values
        $disabled = '';
        $addClass = '';

        if ($this->_isInactive($row)) {
            $qty = '';
            $disabled = 'disabled="disabled" ';
            $addClass = ' input-inactive';
        } else {
            $qty = $row->getData($this->getColumn()->getIndex());
            $qty *= 1;
            if (!$qty) {
                $qty = '';
            }
        }

        // Compose html
        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $qty . '" ' . $disabled;
        $html .= 'class="input-text ' . $this->getColumn()->getInlineCss() . $addClass . '" />';
        return $html;
    }
}
