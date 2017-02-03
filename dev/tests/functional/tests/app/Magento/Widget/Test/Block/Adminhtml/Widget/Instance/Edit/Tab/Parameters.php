<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\ParametersForm;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Widget options form.
 */
class Parameters extends Tab
{
    /**
     * Form selector.
     *
     * @var string
     */
    protected $formSelector = '.fieldset-wide';

    /**
     * Path for widget options tab.
     *
     * @var string
     */
    protected $path = 'Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\\';

    /**
     * Fill Widget options form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $fields['parameters']['value'];
        $path = $this->path . str_replace(' ', '', $fields['code']);
        /** @var ParametersForm $parametersForm */
        $parametersForm = $this->blockFactory->create(
            $path,
            ['element' => $this->_rootElement->find($this->formSelector)]
        );
        $parametersForm->fillForm($data, $element);

        return $this;
    }
}
