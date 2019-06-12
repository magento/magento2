<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
<<<<<<< HEAD
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\ObjectManager;

class Generate extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
=======
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Sitemap\Controller\Adminhtml\Sitemap;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\ObjectManager;

/**
 * Generate sitemap file
 */
class Generate extends Sitemap implements HttpGetActionInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD

=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                $this->messageManager->addSuccessMessage(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t generate the sitemap right now.'));
<<<<<<< HEAD
            } finally {
                $this->appEmulation->stopEnvironmentEmulation();
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
