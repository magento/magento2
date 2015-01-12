<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Logo uploader element renderer
 *
 * @todo Temporary solution.
 * Discuss logo uploader with PO and remove this method.
 * Logo should be assigned to store view level, but not theme.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class LogoUploader extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\ImageUploader
{
    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customization;

    /**
     * Set of templates to render
     *
     * Upper is rendered first and is inserted into next using <?php echo $this->getHtml() ?>
     *
     * @var string[]
     */
    protected $_templates = [
        'Magento_DesignEditor::editor/form/renderer/element/input.phtml',
        'Magento_DesignEditor::editor/form/renderer/logo-uploader.phtml',
    ];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param \Magento\Theme\Model\Config\Customization $customization
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        \Magento\Theme\Model\Config\Customization $customization,
        array $data = []
    ) {
        $this->_themeContext = $themeContext;
        $this->_customization = $customization;
        parent::__construct($context, $data);
    }

    /**
     * Get logo upload url
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getLogoUploadUrl($store)
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/uploadStoreLogo',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId(), 'store_id' => $store->getId()]
        );
    }

    /**
     * Get logo upload url
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getLogoRemoveUrl($store)
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/removeStoreLogo',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId(), 'store_id' => $store->getId()]
        );
    }

    /**
     * Get logo image
     *
     * @param \Magento\Store\Model\Store $store
     * @return string|null
     */
    public function getLogoImage($store)
    {
        $image = null;
        if (null !== $store) {
            $image = basename($this->_scopeConfig->getValue('design/header/logo_src', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId()));
        }
        return $image;
    }

    /**
     * Get stores list
     *
     * @return \Magento\Store\Model\Store|null
     */
    public function getStoresList()
    {
        $stores = $this->_customization->getStoresByThemes();
        return isset(
            $stores[$this->_themeContext->getEditableTheme()->getId()]
        ) ? $stores[$this->_themeContext->getEditableTheme()->getId()] : null;
    }
}
