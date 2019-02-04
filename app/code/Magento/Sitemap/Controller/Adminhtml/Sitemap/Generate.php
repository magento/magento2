<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\ObjectManager;

/**
 * Controller class Generate. Represents requests flow logic for sitemap generation
 */
class Generate extends \Magento\Sitemap\Controller\Adminhtml\Sitemap implements HttpGetActionInterface
{
    /** @var \Magento\Store\Model\App\Emulation $appEmulation */
    private $appEmulation;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param \Magento\Store\Model\App\Emulation|null $appEmulation
     */
    public function __construct(
        Action\Context $context,
        Emulation $appEmulation = null
    ) {
        parent::__construct($context);
        $this->appEmulation = $appEmulation ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\App\Emulation::class);
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
        $sitemap = $this->_objectManager->create(\Magento\Sitemap\Model\Sitemap::class);
        /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                //We need to emulate to get the correct frontend URL for the product images
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $sitemap->generateXml();

                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
