<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCatalogUi\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\MediaContentApi\Model\Config;

/**
 * Controller serving the media gallery content
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Cms::media_gallery';

    /**
     * @var Config
     */
    private $config;

    /**
     * Index constructor.
     * @param Context $context
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Get the media gallery layout
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->config->isEnabled()) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');

            return $resultForward;
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));

        return $resultPage;
    }
}
