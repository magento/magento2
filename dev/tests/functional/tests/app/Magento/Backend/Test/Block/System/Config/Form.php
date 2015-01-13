<?php
/**
 * Store configuration edit form
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Config;

use Mtf\Block\Block;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

class Form extends Block
{
    /**
     * Group block
     *
     * @var string
     */
    protected $groupBlock = '//legend[contains(text(), "%s")]/../..';

    /**
     * Save button
     *
     * @var string
     */
    protected $saveButton = '//button[@data-ui-id="system-config-edit-save-button"]';

    /**
     * Retrieve store configuration form group
     *
     * @param string $name
     * @return Form\Group
     */
    public function getGroup($name)
    {
        $blockFactory = Factory::getBlockFactory();
        $element = $this->_rootElement->find(
            sprintf($this->groupBlock, $name),
            Locator::SELECTOR_XPATH
        );
        return $blockFactory->getMagentoBackendSystemConfigFormGroup($element);
    }

    /**
     * Save store configuration
     */
    public function save()
    {
        $this->_rootElement->find($this->saveButton, Locator::SELECTOR_XPATH)->click();
    }
}
