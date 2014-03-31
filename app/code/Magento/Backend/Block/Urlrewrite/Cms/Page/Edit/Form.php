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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Edit form for CMS page URL rewrites
 *
 * @method \Magento\Cms\Model\Page getCmsPage()
 * @method \Magento\Backend\Block\Urlrewrite\Cms\Page\Edit\Form setCmsPage(\Magento\Cms\Model\Page $model)
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Backend\Block\Urlrewrite\Cms\Page\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Form extends \Magento\Backend\Block\Urlrewrite\Edit\Form
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Cms\Model\Page\UrlrewriteFactory
     */
    protected $_urlRewriteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Core\Model\Source\Urlrewrite\TypesFactory $typesFactory
     * @param \Magento\Core\Model\Source\Urlrewrite\OptionsFactory $optionFactory
     * @param \Magento\Core\Model\Url\RewriteFactory $rewriteFactory
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Cms\Model\Page\UrlrewriteFactory $urlRewriteFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Core\Model\Source\Urlrewrite\TypesFactory $typesFactory,
        \Magento\Core\Model\Source\Urlrewrite\OptionsFactory $optionFactory,
        \Magento\Core\Model\Url\RewriteFactory $rewriteFactory,
        \Magento\Core\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Cms\Model\Page\UrlrewriteFactory $urlRewriteFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = array()
    ) {
        $this->_urlRewriteFactory = $urlRewriteFactory;
        $this->_pageFactory = $pageFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $typesFactory,
            $optionFactory,
            $rewriteFactory,
            $systemStore,
            $adminhtmlData,
            $data
        );
    }

    /**
     * Form post init
     *
     * @param \Magento\Data\Form $form
     * @return \Magento\Backend\Block\Urlrewrite\Cms\Page\Edit\Form
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

        // Fill id path, request path and target path elements
        /** @var $idPath \Magento\Data\Form\Element\AbstractElement */
        $idPath = $this->getForm()->getElement('id_path');
        /** @var $requestPath \Magento\Data\Form\Element\AbstractElement */
        $requestPath = $this->getForm()->getElement('request_path');
        /** @var $targetPath \Magento\Data\Form\Element\AbstractElement */
        $targetPath = $this->getForm()->getElement('target_path');

        $model = $this->_getModel();
        /** @var $cmsPageUrlrewrite \Magento\Cms\Model\Page\Urlrewrite */
        $cmsPageUrlrewrite = $this->_urlRewriteFactory->create();
        if (!$model->getId()) {
            $idPath->setValue($cmsPageUrlrewrite->generateIdPath($cmsPage));

            $sessionData = $this->_getSessionData();
            if (!isset($sessionData['request_path'])) {
                $requestPath->setValue($cmsPageUrlrewrite->generateRequestPath($cmsPage));
            }
            $targetPath->setValue($cmsPageUrlrewrite->generateTargetPath($cmsPage));
            $disablePaths = true;
        } else {
            $cmsPageUrlrewrite->load($this->_getModel()->getId(), 'url_rewrite_id');
            $disablePaths = $cmsPageUrlrewrite->getId() > 0;
        }
        if ($disablePaths) {
            $idPath->setData('disabled', true);
            $targetPath->setData('disabled', true);
        }

        return $this;
    }

    /**
     * Get catalog entity associated stores
     *
     * @return array
     * @throws \Magento\Core\Model\Store\Exception
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
                throw new \Magento\Core\Model\Store\Exception(
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
