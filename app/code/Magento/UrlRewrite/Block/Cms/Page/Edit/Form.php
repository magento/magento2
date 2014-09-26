<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\UrlRewrite\Block\Cms\Page\Edit;

/**
 * Edit form for CMS page URL rewrites
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\UrlRewrite\Block\Edit\Form
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator */
    protected $cmsPageUrlPathGenerator;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\UrlRewrite\Model\OptionProvider $optionProvider
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\UrlRewrite\Model\OptionProvider $optionProvider,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $cmsPageUrlPathGenerator,
        array $data = array()
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
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * Form post init
     *
     * @param \Magento\Framework\Data\Form $form
     * @return \Magento\UrlRewrite\Block\Cms\Page\Edit\Form
     */
    protected function _formPostInit($form)
    {
        $cmsPage = $this->_getCmsPage();
        $form->setAction(
            $this->_adminhtmlData->getUrl(
                'adminhtml/*/save',
                array('id' => $this->_getModel()->getId(), 'cms_page' => $cmsPage->getId())
            )
        );

        // Fill request path and target path elements
        /** @var $requestPath \Magento\Framework\Data\Form\Element\AbstractElement */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var $targetPath \Magento\Framework\Data\Form\Element\AbstractElement */
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
     * @throws \Magento\Framework\App\InitException
     */
    protected function _getEntityStores()
    {
        $cmsPage = $this->_getCmsPage();
        $entityStores = array();

        // showing websites that only associated to CMS page
        if ($this->_getCmsPage()->getId()) {
            $entityStores = (array)$cmsPage->getResource()->lookupStoreIds($cmsPage->getId());
            $this->_requireStoresFilter = !in_array(0, $entityStores);

            if (!$entityStores) {
                throw new \Magento\Framework\App\InitException(
                    __('Chosen cms page does not associated with any website.')
                );
            }
        }

        return $entityStores;
    }

    /**
     * Get CMS page model instance
     *
     * @return \Magento\Cms\Model\Page
     */
    protected function _getCmsPage()
    {
        if (!$this->hasData('cms_page')) {
            $this->setCmsPage($this->_pageFactory->create());
        }
        return $this->getCmsPage();
    }
}
