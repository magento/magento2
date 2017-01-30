<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config\Form\Fieldset\Modules;

/**
 * It displays a list of the inputs with names of the modules on tab Store->Configuration Advanced/Advanced
 * on the store settings page
 *
 * @method \Magento\Config\Block\System\Config\Form getForm()
 * @deprecated because there are tools to change the module state in the admin panel and magento 2 CLI.
 */
class DisableOutput extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dummyElement;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = $this->_moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $this->_eventManager->dispatch(
            'adminhtml_system_config_advanced_disableoutput_render_before',
            ['modules' => $dispatchResult]
        );
        $modules = $dispatchResult->toArray();

        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName === 'Magento_Backend') {
                continue;
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }

    /**
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * @return array
     */
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = [
                ['label' => __('Enable'), 'value' => 0],
                ['label' => __('Disable'), 'value' => 1],
            ];
        }
        return $this->_values;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $moduleName
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName;
        //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }

        $element = $this->_getDummyElement();

        $field = $fieldset->addField(
            $moduleName,
            'select',
            [
                'name' => 'groups[modules_disable_output][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element)
            ]
        )->setRenderer(
            $this->_getFieldRenderer()
        );

        return $field->toHtml();
    }
}
