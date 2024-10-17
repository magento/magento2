<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Cms\Page\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\UrlRewrite\Block\Cms\Page\Edit\Form as CmsPageForm;
use Magento\UrlRewrite\Block\Edit\Form as EditForm;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Edit form for CMS page URL rewrites
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends EditForm
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param OptionProvider $optionProvider
     * @param UrlRewriteFactory $rewriteFactory
     * @param SystemStore $systemStore
     * @param BackendHelper $adminhtmlData
     * @param PageFactory $pageFactory
     * @param CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        OptionProvider $optionProvider,
        UrlRewriteFactory $rewriteFactory,
        SystemStore $systemStore,
        BackendHelper $adminhtmlData,
        PageFactory $pageFactory,
        protected readonly CmsPageUrlPathGenerator $cmsPageUrlPathGenerator,
        array $data = []
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $optionProvider,
            $rewriteFactory,
            $systemStore,
            $adminhtmlData,
            $data
        );
    }

    /**
     * Form post init
     *
     * @param FormData $form
     * @return CmsPageForm
     */
    protected function _formPostInit($form)
    {
        $cmsPage = $this->_getCmsPage();
        $form->setAction(
            $this->_adminhtmlData->getUrl(
                'adminhtml/*/save',
                ['id' => $this->_getModel()->getId(), 'cms_page' => $cmsPage->getId()]
            )
        );

        // Fill request path and target path elements
        /** @var AbstractElement $requestPath */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var AbstractElement $targetPath */
        $targetPath = $this->getForm()->getElement('target_path');

        $model = $this->_getModel();
        if (!$model->getId()) {
            $sessionData = $this->_getSessionData();
            if (!isset($sessionData['request_path'])) {
                $requestPath->setValue($this->cmsPageUrlPathGenerator->getUrlPath($cmsPage));
            }
            $targetPath->setValue($this->cmsPageUrlPathGenerator->getCanonicalUrlPath($cmsPage));
        }
        $targetPath->setData('disabled', true);
        return $this;
    }

    /**
     * Get catalog entity associated stores
     *
     * @return array
     * @throws LocalizedException
     */
    protected function _getEntityStores()
    {
        $cmsPage = $this->_getCmsPage();
        $entityStores = [];

        // showing websites that only associated to CMS page
        if ($this->_getCmsPage()->getId()) {
            $entityStores = (array)$cmsPage->getResource()->lookupStoreIds($cmsPage->getId());
            $this->_requireStoresFilter = !in_array(0, $entityStores);

            if (!$entityStores) {
                throw new LocalizedException(
                    __('Please assign a website to the selected CMS page.')
                );
            }
        }

        return $entityStores;
    }

    /**
     * Get CMS page model instance
     *
     * @return Page
     */
    protected function _getCmsPage()
    {
        if (!$this->hasData('cms_page')) {
            $this->setCmsPage($this->_pageFactory->create());
        }
        return $this->getCmsPage();
    }
}
