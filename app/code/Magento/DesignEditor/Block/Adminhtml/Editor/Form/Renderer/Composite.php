<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Composite form element renderer
 */
class Composite extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Recursive
{
    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     * This templates are made of fieldset.phtml but split into several templates
     *
     * @var string[]
     */
    protected $_templates = [
        'Magento_DesignEditor::editor/form/renderer/composite/children.phtml',
        'Magento_DesignEditor::editor/form/renderer/composite.phtml',
        'Magento_DesignEditor::editor/form/renderer/composite/wrapper.phtml',
    ];

    /**
     * Get CSS classes for element
     *
     * Used in composite.phtml
     *
     * @return string[]
     */
    public function getCssClasses()
    {
        /** @var $element \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Composite\AbstractComposite */
        $element = $this->getElement();
        $isField = $element->getFieldsetType() == 'field';

        $cssClasses = [];
        $cssClasses[] = $isField ? 'field' : 'fieldset';
        if ($element->getClass()) {
            $cssClasses[] = $element->getClass();
        }
        if ($isField && $element->hasAdvanced()) {
            $cssClasses[] = 'complex';
        }

        return $cssClasses;
    }
}
