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
 * @category    Mage
 * @package     Mage_Rule
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Rule_Model_Action_Collection extends Mage_Rule_Model_Action_Abstract
{
    /**
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param array $data
     */
    public function __construct(Mage_Core_Model_View_Url $viewUrl, array $data = array())
    {
        parent::__construct($viewUrl, $data);
        $this->setActions(array());
        $this->setType('Mage_Rule_Model_Action_Collection');
    }

    /**
     * Returns array containing actions in the collection
     *
     * Output example:
     * array(
     *   {action::asArray},
     *   {action::asArray}
     * )
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray();

        foreach ($this->getActions() as $item) {
            $out['actions'][] = $item->asArray();
        }
        return $out;
    }

    public function loadArray(array $arr)
    {
        if (!empty($arr['actions']) && is_array($arr['actions'])) {
            foreach ($arr['actions'] as $actArr) {
                if (empty($actArr['type'])) {
                    continue;
                }
                $action = Mage::getModel($actArr['type']);
                $action->loadArray($actArr);
                $this->addAction($action);
            }
        }
        return $this;
    }

    public function addAction(Mage_Rule_Model_Action_Interface $action)
    {
        $actions = $this->getActions();

        $action->setRule($this->getRule());

        $actions[] = $action;
        if (!$action->getId()) {
            $action->setId($this->getId().'.'.sizeof($actions));
        }

        $this->setActions($actions);
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->toHtml().'Perform following actions: ';
        if ($this->getId()!='1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getNewChildElement()
    {
        return $this->getForm()->addField('action:' . $this->getId() . ':new_child', 'select', array(
            'name' => 'rule[actions][' . $this->getId() . '][new_child]',
            'values' => $this->getNewChildSelectOptions(),
            'value_name' => $this->getNewChildName(),
        ))->setRenderer(Mage::getBlockSingleton('Mage_Rule_Block_Newchild'));
    }

    /**
     * @return string
     */
    public function asHtmlRecursive()
    {
        $html = $this->asHtml() . '<ul id="action:' . $this->getId() . ':children">';
        foreach ($this->getActions() as $cond) {
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }

    /**
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asString($format = '')
    {
        $str = Mage::helper('Mage_Rule_Helper_Data')->__("Perform following actions");
        return $str;
    }

    /**
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = $this->asString();
        foreach ($this->getActions() as $action) {
            $str .= "\n" . $action->asStringRecursive($level + 1);
        }
        return $str;
    }

    /**
     * @return $this
     */
    public function process()
    {
        foreach ($this->getActions() as $action) {
            $action->process();
        }
        return $this;
    }
}
