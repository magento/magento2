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
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Grid column widget for rendering action grid cells
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Renders column
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if (sizeof($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">' .
            '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            if (is_array($action)) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }

    /**
     * Render single action as dropdown option html
     *
     * @param array $action
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _toOptionHtml($action, \Magento\Framework\Object $row)
    {
        $actionAttributes = new \Magento\Framework\Object();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        $htmlAttibutes = array('value' => $this->escapeHtml($this->_jsonEncoder->encode($action)));
        $actionAttributes->setData($htmlAttibutes);
        return '<option ' . $actionAttributes->serialize() . '>' . $actionCaption . '</option>';
    }

    /**
     * Render single action as link html
     *
     * @param array $action
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _toLinkHtml($action, \Magento\Framework\Object $row)
    {
        $actionAttributes = new \Magento\Framework\Object();

        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if (isset($action['confirm'])) {
            $action['onclick'] = 'return window.confirm(\'' . addslashes(
                $this->escapeHtml($action['confirm'])
            ) . '\')';
            unset($action['confirm']);
        }

        $actionAttributes->setData($action);
        return '<a ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
    }

    /**
     * Prepares action data for html render
     *
     * @param array &$action
     * @param string &$actionCaption
     * @param \Magento\Framework\Object $row
     * @return $this
     */
    protected function _transformActionData(&$action, &$actionCaption, \Magento\Framework\Object $row)
    {
        foreach ($action as $attribute => $value) {
            if (isset($action[$attribute]) && !is_array($action[$attribute])) {
                $this->getColumn()->setFormat($action[$attribute]);
                $action[$attribute] = parent::render($row);
            } else {
                $this->getColumn()->setFormat(null);
            }

            switch ($attribute) {
                case 'caption':
                    $actionCaption = $action['caption'];
                    unset($action['caption']);
                    break;

                case 'url':
                    if (is_array($action['url']) && isset($action['field'])) {
                        $params = array($action['field'] => $this->_getValue($row));
                        if (isset($action['url']['params'])) {
                            $params = array_merge($action['url']['params'], $params);
                        }
                        $action['href'] = $this->getUrl($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }
                    unset($action['url']);
                    break;

                case 'popup':
                    $action['onclick'] = 'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,scrollbars=1\');return false;';
                    break;
            }
        }
        return $this;
    }
}
