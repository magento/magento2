<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    protected $path = 'Magento\*\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\\';

    /**
     * Fill Widget options form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $data = $fields['parameters']['value'];
        /** @var ParametersForm $parametersForm */
        $parametersForm = $this->blockFactory->create(
            $this->getElementClass($fields),
            ['element' => $this->_rootElement->find($this->formSelector)]
        );
        $parametersForm->fillForm($data, $element);

        return $this;
    }

    /**
     * Get element class.
     *
     * @param array $fields
     * @return string
     */
    private function getElementClass(array $fields)
    {
        $path = $this->path . str_replace(' ', '', $fields['code']) . '.php';
        $path = str_replace('\\', DIRECTORY_SEPARATOR, MTF_TESTS_PATH . $path);
        $paths = glob($path);
        $path = str_replace([MTF_TESTS_PATH, '.php'], '', $paths[0]);

        return str_replace('/', '\\', $path);
    }
}
