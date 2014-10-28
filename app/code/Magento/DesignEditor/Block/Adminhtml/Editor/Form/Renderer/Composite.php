<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_templates = array(
        'Magento_DesignEditor::editor/form/renderer/composite/children.phtml',
        'Magento_DesignEditor::editor/form/renderer/composite.phtml',
        'Magento_DesignEditor::editor/form/renderer/composite/wrapper.phtml'
    );

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

        $cssClasses = array();
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
