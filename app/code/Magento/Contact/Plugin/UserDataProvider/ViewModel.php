<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Plugin\UserDataProvider;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Sets the view model
 */
class ViewModel
{
    /**
     * @var ArgumentInterface
     */
    private $argument;

    /**
     * @param ArgumentInterface $argument
     */
    public function __construct(ArgumentInterface $argument)
    {
        $this->argument = $argument;
    }

    /**
     * Sets the view model
     *
     * @param DataObject $dataObject
     * @return null
     */
    public function beforeToHtml(DataObject $dataObject)
    {
        $dataObject->setData('view_model', $this->argument);
        return null;
    }
}
