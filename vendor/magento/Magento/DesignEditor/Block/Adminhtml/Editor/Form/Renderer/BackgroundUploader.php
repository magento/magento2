<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer;

/**
 * Color-picker form element renderer
 */
class BackgroundUploader extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer
{
    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        array $data = []
    ) {
        $this->_themeContext = $themeContext;
        parent::__construct($context, $data);
    }

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Magento_DesignEditor::editor/form/renderer/background-uploader.phtml';

    /**
     * Get URL of image upload action
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/uploadQuickStyleImage',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }

    /**
     * Get URL of remove image action
     *
     * @return string
     */
    public function getImageRemoveUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/removeQuickStyleImage',
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }
}
