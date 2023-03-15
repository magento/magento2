<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;
use Magento\Sitemap\Model\Sitemap as ModelSitemap;
use Magento\Store\Model\App\Emulation;

/**
 * Generate sitemap file
 */
class Generate extends Sitemap implements HttpGetActionInterface
{
    /**
     * @param Action\Context $context
     * @param Emulation $appEmulation
     */
    public function __construct(
        Action\Context $context,
        private readonly Emulation $appEmulation
    ) {
        parent::__construct($context);
    }

    /**
     * Generate sitemap
     *
     * @return void
     */
    public function execute()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = $this->_objectManager->create(ModelSitemap::class);
        /* @var ModelSitemap $sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    Area::AREA_FRONTEND,
                    true
                );
                $sitemap->generateXml();
                $this->appEmulation->stopEnvironmentEmulation();
                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
