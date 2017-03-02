<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Block\Adminhtml\Template\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Click Load button in Email template form.
 * this class needs to be created because we need a customized click on the 'Load' button, its not a standard click
 */
class TemplateForm extends Form
{
    private $loadButton = '#load';

    /**
     * @return void
     */
    public function clickLoadTemplate()
    {
        $element = $this->_rootElement->find($this->loadButton, Locator::SELECTOR_CSS); // locate the Load button
        $element->click(); // click the load button
    }
}
