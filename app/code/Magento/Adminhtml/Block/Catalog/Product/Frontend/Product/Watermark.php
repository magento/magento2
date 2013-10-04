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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fieldset config form element renderer
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Frontend\Product;

class Watermark
    extends \Magento\Backend\Block\AbstractBlock
    implements \Magento\Data\Form\Element\Renderer\RendererInterface
{
    const XML_PATH_IMAGE_TYPES = 'global/catalog/product/media/image_types';

    /**
     * @var \Magento\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Backend\Block\System\Config\Form\Field
     */
    protected $_formField;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Watermark\Position
     */
    protected $_watermarkPosition;

    /**
     * @param \Magento\Catalog\Model\Config\Source\Watermark\Position $watermarkPosition
     * @param \Magento\Backend\Block\System\Config\Form\Field $formField
     * @param \Magento\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Core\Model\Config $coreConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Config\Source\Watermark\Position $watermarkPosition,
        \Magento\Backend\Block\System\Config\Form\Field $formField,
        \Magento\Data\Form\Element\Factory $elementFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Core\Model\Config $coreConfig,
        array $data = array()
    ) {
        $this->_watermarkPosition = $watermarkPosition;
        $this->_formField = $formField;
        $this->_elementFactory = $elementFactory;
        $this->_coreConfig = $coreConfig;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $attributes = $this->_coreConfig->getNode(self::XML_PATH_IMAGE_TYPES)->asArray();

        foreach ($attributes as $key => $attribute) {
            /**
             * Watermark size field
             */
            /** @var \Magento\Data\Form\Element\Text $field */
            $field = $this->_elementFactory->create('text');
            $field->setName("groups[watermark][fields][{$key}_size][value]")
                ->setForm($this->getForm())
                ->setLabel(__('Size for %1', $attribute['title']))
                ->setRenderer($this->_formField);
            $html.= $field->toHtml();

            /**
             * Watermark upload field
             */
            /** @var \Magento\Data\Form\Element\Imagefile $field */
            $field = $this->_elementFactory->create('imagefile');
            $field->setName("groups[watermark][fields][{$key}_image][value]")
                ->setForm($this->getForm())
                ->setLabel(__('Watermark File for %1', $attribute['title']))
                ->setRenderer($this->_formField);
            $html.= $field->toHtml();

            /**
             * Watermark position field
             */
            /** @var \Magento\Data\Form\Element\Select $field */
            $field = $this->_elementFactory->create('select');
            $field->setName("groups[watermark][fields][{$key}_position][value]")
                ->setForm($this->getForm())
                ->setLabel(__('Position of Watermark for %1', $attribute['title']))
                ->setRenderer($this->_formField)
                ->setValues($this->_watermarkPosition->toOptionArray());
            $html.= $field->toHtml();
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getHeaderHtml($element)
    {
        $id = $element->getHtmlId();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');

        $html = '<h4 class="icon-head head-edit-form">'.$element->getLegend().'</h4>';
        $html.= '<fieldset class="config" id="'.$element->getHtmlId().'">';
        $html.= '<legend>'.$element->getLegend().'</legend>';

        // field label column
        $html.= '<table cellspacing="0"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html.= '<colgroup class="use-default" />';
        }
        $html.= '<tbody>';

        return $html;
    }

    protected function _getFooterHtml($element)
    {
        $html = '</tbody></table></fieldset>';
        return $html;
    }
}
