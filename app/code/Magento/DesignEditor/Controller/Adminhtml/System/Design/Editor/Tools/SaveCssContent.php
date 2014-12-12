<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class SaveCssContent extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Save custom css file
     *
     * @return void
     */
    public function execute()
    {
        $customCssContent = (string)$this->getRequest()->getParam('custom_css_content', '');
        /** @var $cssService \Magento\Theme\Model\Theme\Customization\File\CustomCss */
        $cssService = $this->_objectManager->get('Magento\Theme\Model\Theme\Customization\File\CustomCss');
        /** @var $singleFile \Magento\Theme\Model\Theme\SingleFile */
        $singleFile = $this->_objectManager->create(
            'Magento\Theme\Model\Theme\SingleFile',
            ['fileService' => $cssService]
        );
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $customCss = $singleFile->update($editableTheme, $customCssContent);
            $response = [
                'success' => true,
                'filename' => $customCss->getFileName(),
                'message' => __('You updated the %1 file.', $customCss->getFileName()),
            ];
        } catch (CoreException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('We can\'t save the custom css file.')];
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }
}
