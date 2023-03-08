<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use JsonSerializable;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Action
 */
class Action extends AbstractComponent
{
    const NAME = 'action';

    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|JsonSerializable $actions
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        protected $actions = null
    ) {
        parent::__construct($context, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        if (!empty($this->actions)) {
            $this->setData('config', array_replace_recursive(['actions' => $this->actions], $this->getConfiguration()));
        }

        parent::prepare();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
