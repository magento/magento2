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

namespace Magento\Backend\Block\Widget\Button\Toolbar;

use \Magento\Backend\Block\Widget\Button\ContextInterface;

/**
 * @method \Magento\Backend\Block\Widget\Button\Item getButtonItem
 * @method ContextInterface getContext
 * @method ContextInterface setContext(ContextInterface $context)
 */
class Container extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Create button renderer
     *
     * @param string $blockName
     * @param string $blockClassName
     * @return \Magento\Backend\Block\Widget\Button
     */
    protected function createButton($blockName, $blockClassName = null)
    {
        if (null === $blockClassName) {
            $blockClassName = 'Magento\Backend\Block\Widget\Button';
        }
        return $this->getLayout()->createBlock($blockClassName, $blockName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $item = $this->getButtonItem();
        $context = $this->getContext();

        if ($item && $context && $context->canRender($item)) {
            $data = $item->getData();
            $blockClassName = isset($data['class_name']) ? $data['class_name'] : null;
            $buttonName = $this->getContext()->getNameInLayout() . '-' . $item->getId() . '-button';
            $block = $this->createButton($buttonName, $blockClassName);
            $block->setData($data);
            return $block->toHtml();
        }
        return parent::_toHtml();
    }
}
