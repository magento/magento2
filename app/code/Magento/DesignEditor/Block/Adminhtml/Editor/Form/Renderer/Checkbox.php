<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Checkbox form element renderer
 */
class Checkbox extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Recursive
{
    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     *
     * @var string[]
     */
    protected $_templates = [
        'Magento_DesignEditor::editor/form/renderer/element/input.phtml',
        'Magento_DesignEditor::editor/form/renderer/checkbox-utility.phtml',
        'Magento_DesignEditor::editor/form/renderer/element/wrapper.phtml',
        'Magento_DesignEditor::editor/form/renderer/template.phtml',
    ];
}
