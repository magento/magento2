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
namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * Grid widget massaction single action item
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Backend\Block\Widget
{
    /**
     * @var Extended
     */
    protected $_massaction = null;

    /**
     * Set parent massaction block
     *
     * @param  Extended $massaction
     * @return $this
     */
    public function setMassaction($massaction)
    {
        $this->_massaction = $massaction;
        return $this;
    }

    /**
     * Retrieve parent massaction block
     *
     * @return Extended
     */
    public function getMassaction()
    {
        return $this->_massaction;
    }

    /**
     * Set additional action block for this item
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setAdditionalActionBlock($block)
    {
        if (is_string($block)) {
            $block = $this->getLayout()->createBlock($block);
        } elseif (is_array($block)) {
            $block = $this->_createFromConfig($block);
        } elseif (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new \Magento\Framework\Model\Exception('Unknown block type');
        }

        $this->setChild('additional_action', $block);
        return $this;
    }

    /**
     * @param array $config
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _createFromConfig(array $config)
    {
        $type = isset($config['type']) ? $config['type'] : 'default';
        switch ($type) {
            default:
                $blockClass = 'Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional\DefaultAdditional';
                break;
        }

        $block = $this->getLayout()->createBlock($blockClass);
        $block->createFromConfiguration(isset($config['type']) ? $config['config'] : $config);
        return $block;
    }

    /**
     * Retrieve additional action block for this item
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getAdditionalActionBlock()
    {
        return $this->getChildBlock('additional_action');
    }

    /**
     * Retrieve additional action block HTML for this item
     *
     * @return string
     */
    public function getAdditionalActionBlockHtml()
    {
        return $this->getChildHtml('additional_action');
    }
}
