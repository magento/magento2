<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
