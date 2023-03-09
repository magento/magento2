<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Exception;
use InvalidArgumentException;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Psr\Log\LoggerInterface;

/**
 * The admin area controller to delete theme.
 *
 * @deprecated 100.2.0
 */
class Delete extends Theme implements HttpGetActionInterface
{
    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('id');
        try {
            if ($themeId) {
                /** @var ThemeInterface $theme */
                $theme = $this->_objectManager->create(
                    ThemeInterface::class
                )->load($themeId);
                if (!$theme->getId()) {
                    throw new InvalidArgumentException(__(
                        'We cannot find a theme with id "%1".',
                        $themeId
                    )->render());
                }
                if (!$theme->isVirtual()) {
                    throw new InvalidArgumentException(
                        sprintf('Only virtual theme is possible to delete and theme "%s" isn\'t virtual', $themeId)
                    );
                }
                $theme->delete();
                $this->messageManager->addSuccess(__('You deleted the theme.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('We cannot delete the theme.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
