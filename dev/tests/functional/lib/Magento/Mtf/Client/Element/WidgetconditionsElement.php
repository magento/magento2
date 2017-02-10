<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Client\Element;

/**
 * @inheritdoc
 */
class WidgetconditionsElement extends ConditionsElement
{
    /**
     * Rule param input selector.
     *
     * @var string
     */
    protected $ruleParamInput = '[name^="parameters"]';
}
