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
 * Catalog textarea attribute WYSIWYG button
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Helper\Form;

class Wysiwyg extends \Magento\Data\Form\Element\Textarea
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * Catalog data
     *
     * @var \Magento\Core\Model\ModuleManager
     */
    protected $_moduleManager = null;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Core\Model\ModuleManager $moduleManager
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $attributes
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\View\LayoutInterface $layout,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Core\Model\ModuleManager $moduleManager,
        \Magento\Backend\Helper\Data $backendData,
        array $attributes = array()
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_layout = $layout;
        $this->_moduleManager = $moduleManager;
        $this->_backendData = $backendData;
        parent::__construct($coreData, $factoryElement, $factoryCollection, $attributes);
    }

    /**
     * Retrieve additional html and put it at the end of element html
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        if ($this->getIsWysiwygEnabled()) {
            $disabled = ($this->getDisabled() || $this->getReadonly());
            $html .= $this->_layout->createBlock('Magento\Adminhtml\Block\Widget\Button', '', array('data' => array(
                    'label'   => __('WYSIWYG Editor'),
                    'type'    => 'button',
                    'disabled' => $disabled,
                    'class' => ($disabled) ? 'disabled action-wysiwyg' : 'action-wysiwyg',
                    'onclick' => 'catalogWysiwygEditor.open(\''
                        . $this->_backendData->getUrl('adminhtml/catalog_product/wysiwyg')
                        . '\', \'' . $this->getHtmlId().'\')'
                )))->toHtml();
            $html .= <<<HTML
<script type="text/javascript">
jQuery('#{$this->getHtmlId()}')
    .addClass('wysiwyg-editor')
    .data(
        'wysiwygEditor',
        new tinyMceWysiwygSetup(
            '{$this->getHtmlId()}',
             {
                settings: {
                    theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                        'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                    theme_advanced_buttons2: null,
                    theme_advanced_buttons3: null,
                    theme_advanced_buttons4: null,
                    theme_advanced_statusbar_location: null
                }
            }
        ).turnOn()
    );
</script>
HTML;
        }
        return $html;
    }

    /**
     * Check whether wysiwyg enabled or not
     *
     * @return boolean
     */
    public function getIsWysiwygEnabled()
    {
        if ($this->_moduleManager->isEnabled('Magento_Cms')) {
            return (bool)($this->_wysiwygConfig->isEnabled()
                && $this->getEntityAttribute()->getIsWysiwygEnabled());
        }

        return false;
    }
}
