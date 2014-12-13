<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class SaveImageSizing extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Save image sizes
     *
     * @return void
     */
    public function execute()
    {
        $imageSizing = $this->getRequest()->getParam('imagesizing');
        /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
        $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');
        /** @var $imageSizingValidator \Magento\DesignEditor\Model\Editor\Tools\ImageSizing\Validator */
        $imageSizingValidator = $this->_objectManager->get(
            'Magento\DesignEditor\Model\Editor\Tools\ImageSizing\Validator'
        );
        try {
            $themeContext = $this->_initContext();
            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_IMAGE_SIZING,
                $themeContext->getStagingTheme(),
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $imageSizing = $imageSizingValidator->validate($configuration->getAllControlsData(), $imageSizing);
            $configuration->saveData($imageSizing);
            $result = ['success' => true, 'message' => __('We saved the image sizes.')];
        } catch (CoreException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('We can\'t save image sizes.')];
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
