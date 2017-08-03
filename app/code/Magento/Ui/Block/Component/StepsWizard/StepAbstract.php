<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

/**
 * Abstract block for multi-step wizard UI
 * @since 2.0.0
 */
abstract class StepAbstract extends \Magento\Framework\View\Element\Template implements StepInterface
{
    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->toHtml();
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getParentComponentName()
    {
        return $this->getParentBlock()->getComponentName();
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getComponentName()
    {
        if (null === $this->getData('component_name')) {
            $this->setData('component_name', $this->getParentComponentName() . '_' . $this->getNameInLayout());
        }
        return $this->getData('component_name');
    }
}
