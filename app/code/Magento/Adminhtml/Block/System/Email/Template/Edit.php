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
 * Adminhtml system template edit block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method array getTemplateOptions()
 */
namespace Magento\Adminhtml\Block\System\Email\Template;

class Edit extends \Magento\Adminhtml\Block\Widget
{
    /**
     * @var \Magento\Core\Model\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Backend\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var \Magento\Core\Model\Email\Template\Config
     */
    private $_emailConfig;

    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'system/email/template/edit.phtml';
    
    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Core\Model\Email\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Core\Model\Email\Template\Config $emailConfig,
        array $data = array()
    ) {
        $this->_registryManager = $registry;
        $this->_menuConfig = $menuConfig;
        $this->_configStructure = $configStructure;
        $this->_emailConfig = $emailConfig;
        parent::__construct($coreData, $context, $data);
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Back'),
                        'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                        'class'   => 'back'
                    )
                )
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Reset'),
                        'onclick' => 'window.location.href = window.location.href'
                    )
                )
        );

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Delete Template'),
                        'onclick' => 'templateControl.deleteTemplate();',
                        'class'   => 'delete'
                    )
                )
        );

        $this->setChild('to_plain_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Convert to Plain Text'),
                        'onclick' => 'templateControl.stripTags();',
                        'id'      => 'convert_button'
                    )
                )
        );

        $this->setChild('to_html_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Return Html Version'),
                        'onclick' => 'templateControl.unStripTags();',
                        'id'      => 'convert_button_back',
                        'style'   => 'display:none'
                    )
                )
        );

        $this->setChild('toggle_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Toggle Editor'),
                        'onclick' => 'templateControl.toggleEditor();',
                        'id'      => 'toggle_button'
                    )
                )
        );

        $this->setChild('preview_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Preview Template'),
                        'onclick' => 'templateControl.preview();'
                    )
                )
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Save Template'),
                        'onclick' => 'templateControl.save();',
                        'class'   => 'save'
                    )
                )
        );

        $this->setChild('load_button',
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Widget\Button')
                ->setData(
                    array(
                        'label'   => __('Load Template'),
                        'onclick' => 'templateControl.load();',
                        'type'    => 'button',
                        'class'   => 'save'
                    )
                )
        );

        $this->addChild('form', 'Magento\Adminhtml\Block\System\Email\Template\Edit\Form');
        return parent::_prepareLayout();
    }

    /**
     * Collect, sort and set template options
     *
     * @return \Magento\Adminhtml\Block\System\Email\Template\Edit
     */
    protected function _beforeToHtml()
    {
        $groupedOptions = array();
        foreach ($this->_getDefaultTemplatesAsOptionsArray() as $option) {
            $groupedOptions[$option['group']][] = $option;
        }
        ksort($groupedOptions);
        $this->setData('template_options', $groupedOptions);

        return parent::_beforeToHtml();
    }

    /**
     * Get default templates as options array
     *
     * @return array
     */
    protected function _getDefaultTemplatesAsOptionsArray()
    {
        $options = array(array('value' => '', 'label' => '', 'group' => ''));
        foreach ($this->_emailConfig->getAvailableTemplates() as $templateId) {
            $options[] = array(
                'value' => $templateId,
                'label' => $this->_emailConfig->getTemplateLabel($templateId),
                'group' => $this->_emailConfig->getTemplateModule($templateId),
            );
        }
        uasort($options, function (array $firstElement, array $secondElement) {
            return strcmp($firstElement['label'], $secondElement['label']);
        });
        return $options;
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getToggleButtonHtml()
    {
        return $this->getChildHtml('toggle_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getToPlainButtonHtml()
    {
        return $this->getChildHtml('to_plain_button');
    }

    public function getToHtmlButtonHtml()
    {
        return $this->getChildHtml('to_html_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getLoadButtonHtml()
    {
        return $this->getChildHtml('load_button');
    }

    /**
     * Return edit flag for block
     *
     * @return boolean
     */
    public function getEditMode()
    {
        return $this->getEmailTemplate()->getId();
    }

    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEditMode()) {
            return __('Edit Email Template');
        }
        return  __('New Email Template');
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }

    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true));
    }

    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    public function isTextType()
    {
        return $this->getEmailTemplate()->isPlain();
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current' => true));
    }

    /**
     * Retrive email template model
     *
     * @return \Magento\Core\Model\Email\Template
     */
    public function getEmailTemplate()
    {
        return $this->_registryManager->registry('current_email_template');
    }

    /**
     * Load template url
     *
     * @return string
     */
    public function getLoadUrl()
    {
        return $this->getUrl('*/*/defaultTemplate');
    }

    /**
     * Get paths of where current template is used as default
     *
     * @param bool $asJSON
     * @return string
     */
    public function getUsedDefaultForPaths($asJSON = true)
    {
        /** @var $template \Magento\Adminhtml\Model\Email\Template */
        $template = $this->getEmailTemplate();
        $paths = $template->getSystemConfigPathsWhereUsedAsDefault();
        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        if ($asJSON) {
            return $this->helper('Magento\Core\Helper\Data')->jsonEncode($pathsParts);
        }
        return $pathsParts;
    }

    /**
     * Get paths of where current template is currently used
     *
     * @param bool $asJSON
     * @return string
     */
    public function getUsedCurrentlyForPaths($asJSON = true)
    {
        /** @var $template \Magento\Adminhtml\Model\Email\Template */
        $template = $this->getEmailTemplate();
        $paths = $template->getSystemConfigPathsWhereUsedCurrently();
        $pathsParts = $this->_getSystemConfigPathsParts($paths);
        if ($asJSON) {
            return $this->_coreData->jsonEncode($pathsParts);
        }
        return $pathsParts;
    }

    /**
     * Convert xml config pathes to decorated names
     *
     * @param array $paths
     * @return array
     */
    protected function _getSystemConfigPathsParts($paths)
    {
        $result = $urlParams = $prefixParts = array();
        $scopeLabel = __('GLOBAL');
        if ($paths) {
            /** @var $menu \Magento\Backend\Model\Menu */
            $menu = $this->_menuConfig->getMenu();
            $item = $menu->get('Magento_Adminhtml::system');
            // create prefix path parts
            $prefixParts[] = array(
                'title' => __($item->getTitle()),
            );
            $item = $menu->get('Magento_Adminhtml::system_config');
            $prefixParts[] = array(
                'title' => __($item->getTitle()),
                'url' => $this->getUrl('adminhtml/system_config/'),
            );

            $pathParts = $prefixParts;
            foreach ($paths as $pathData) {
                $pathDataParts = explode('/', $pathData['path']);
                $sectionName = array_shift($pathDataParts);

                $urlParams = array('section' => $sectionName);
                if (isset($pathData['scope']) && isset($pathData['scope_id'])) {
                    switch ($pathData['scope']) {
                        case 'stores':
                            $store = $this->_storeManager->getStore($pathData['scope_id']);
                            if ($store) {
                                $urlParams['website'] = $store->getWebsite()->getCode();
                                $urlParams['store'] = $store->getCode();
                                $scopeLabel = $store->getWebsite()->getName() . '/' . $store->getName();
                            }
                            break;
                        case 'websites':
                            $website = $this->_storeManager->getWebsite($pathData['scope_id']);
                            if ($website) {
                                $urlParams['website'] = $website->getCode();
                                $scopeLabel = $website->getName();
                            }
                            break;
                        default:
                            break;
                    }
                }
                $pathParts[] = array(
                    'title' => $this->_configStructure->getElement($sectionName)->getLabel(),
                    'url' => $this->getUrl('adminhtml/system_config/edit', $urlParams),
                );
                $elementPathParts = array($sectionName);
                while (count($pathDataParts) != 1) {
                    $elementPathParts[] = array_shift($pathDataParts);
                    $pathParts[] = array(
                        'title' => $this->_configStructure
                            ->getElementByPathParts($elementPathParts)
                            ->getLabel()
                    );
                }
                $elementPathParts[] = array_shift($pathDataParts);
                $pathParts[] = array(
                    'title' => $this->_configStructure
                        ->getElementByPathParts($elementPathParts)
                        ->getLabel(),
                    'scope' => $scopeLabel
                );
                $result[] = $pathParts;
                $pathParts = $prefixParts;
            }
        }
        return $result;
    }

    /**
     * Return original template code of current template
     *
     * @return string
     */
    public function getOrigTemplateCode()
    {
        return $this->getEmailTemplate()->getOrigTemplateCode();
    }
}
