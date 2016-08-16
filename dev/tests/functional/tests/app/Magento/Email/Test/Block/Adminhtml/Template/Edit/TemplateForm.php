<?php

namespace Magento\Email\Test\Block\Adminhtml\Template\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Synonyms edit form in admin.
 */
/* this class needs to be created becuase we have a customized click on the 'Load' button, its not a standard click
*/
class TemplateForm extends Form
{
    protected $loadButton = "#load";

    public function clickLoadTemplate() {
        $element = $this->_rootElement->find($this->loadButton, Locator::SELECTOR_CSS); // find this element
        $element->click(); // perform the action
    }
}