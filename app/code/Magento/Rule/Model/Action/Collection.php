<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Action;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends AbstractAction
{
    /**
     * @var \Magento\Rule\Model\ActionFactory
     * @since 2.0.0
     */
    protected $_actionFactory;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Rule\Model\ActionFactory $actionFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Rule\Model\ActionFactory $actionFactory,
        array $data = []
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_layout = $layout;

        parent::__construct($assetRepo, $layout, $data);

        $this->setActions([]);
        $this->setType(\Magento\Rule\Model\Action\Collection::class);
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
     * @since 2.0.0
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();

        foreach ($this->getActions() as $item) {
            $out['actions'][] = $item->asArray();
        }
        return $out;
    }

    /**
     * @param array $arr
     * @return $this
     * @since 2.0.0
     */
    public function loadArray(array $arr)
    {
        if (!empty($arr['actions']) && is_array($arr['actions'])) {
            foreach ($arr['actions'] as $actArr) {
                if (empty($actArr['type'])) {
                    continue;
                }
                $action = $this->_actionFactory->create($actArr['type']);
                $action->loadArray($actArr);
                $this->addAction($action);
            }
        }
        return $this;
    }

    /**
     * @param ActionInterface $action
     * @return $this
     * @since 2.0.0
     */
    public function addAction(ActionInterface $action)
    {
        $actions = $this->getActions();

        $action->setRule($this->getRule());

        $actions[] = $action;
        if (!$action->getId()) {
            $action->setId($this->getId() . '.' . sizeof($actions));
        }

        $this->setActions($actions);
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->toHtml() . 'Perform following actions: ';
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':new_child',
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Newchild::class)
        );
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function asString($format = '')
    {
        $str = __("Perform following actions");
        return $str;
    }

    /**
     * @param int $level
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function process()
    {
        foreach ($this->getActions() as $action) {
            $action->process();
        }
        return $this;
    }
}
