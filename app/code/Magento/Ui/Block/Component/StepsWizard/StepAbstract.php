<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Component\StepsWizard;

abstract class StepAbstract extends \Magento\Framework\View\Element\Template implements StepInterface
{
    /**
     * Get step id
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->getParentComponentName() . '_' . $this->getNameInLayout());
        }
        return $this->getData('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->toHtml();
    }

    /**
     * Get json data
     * @return string json
     */
    public function getJsonData()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getParentComponentName()
    {
        return $this->getParentBlock()->getComponentName();
    }
}
