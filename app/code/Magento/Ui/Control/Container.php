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
namespace Magento\Ui\Control;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Container
 */
class Container extends AbstractBlock
{
    /**
     * Default button class
     */
    const DEFAULT_BUTTON = 'Magento\Ui\Control\Button';

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
            $blockClassName = static::DEFAULT_BUTTON;
        }

        return $this->getLayout()->createBlock($blockClassName, $blockName);
    }

    /**
     * Render element HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var \Magento\Ui\Control\Item $item */
        $item = $this->getButtonItem();
        $data = $item->getData();

        $block = $this->createButton(
            $this->getData('context')->getNameInLayout() . '-' . $item->getId() . '-button',
            isset($data['class_name']) ? $data['class_name'] : null
        );
        $block->setData($data);

        return $block->toHtml();
    }
}
