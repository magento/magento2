<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\Role\Tab;

use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Rolesedit Tab Display Block.
 *
 * @api
 */
class Edit extends \Magento\Backend\Block\Widget\Form implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'role/edit.phtml';

    /**
     * Root ACL Resource
     *
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResource;

    /**
     * Rules collection factory
     *
     * @var \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory
     */
    protected $_rulesCollectionFactory;

    /**
     * Acl builder
     *
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $_aclRetriever;

    /**
     * Acl resource provider
     *
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     */
    protected $_aclResourceProvider;

    /**
     * @var \Magento\Integration\Helper\Data
     */
    protected $_integrationData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.1.0
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider,
        \Magento\Integration\Helper\Data $integrationData,
        array $data = []
    ) {
        $this->_aclRetriever = $aclRetriever;
        $this->_rootResource = $rootResource;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_aclResourceProvider = $aclResourceProvider;
        $this->_integrationData = $integrationData;
        parent::__construct($context, $data);
    }

    /**
     * Set core registry
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function setCoreRegistry(\Magento\Framework\Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get core registry
     *
     * @return \Magento\Framework\Registry
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    public function getCoreRegistry()
    {
        if (!($this->coreRegistry instanceof \Magento\Framework\Registry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Registry::class);
        } else {
            return $this->coreRegistry;
        }
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Role Resources');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check if everything is allowed
     *
     * @return bool
     */
    public function isEverythingAllowed()
    {
        $selectedResources = $this->getSelectedResources();
        $id = $this->_rootResource->getId();
        return in_array($id, $selectedResources);
    }

    /**
     * Get selected resources
     *
     * @return array|mixed|\string[]
     * @since 2.1.0
     */
    public function getSelectedResources()
    {
        $selectedResources = $this->getData('selected_resources');
        if (empty($selectedResources)) {
            $allResource = $this->getCoreRegistry()->registry(SaveRole::RESOURCE_ALL_FORM_DATA_SESSION_KEY);
            if ($allResource) {
                $selectedResources = [$this->_rootResource->getId()];
            } else {
                $selectedResources = $this->getCoreRegistry()->registry(SaveRole::RESOURCE_FORM_DATA_SESSION_KEY);
            }

            if (null === $selectedResources) {
                $rid = $this->_request->getParam('rid', false);
                $selectedResources = $this->_aclRetriever->getAllowedResourcesByRole($rid);
            }

            $this->setData('selected_resources', $selectedResources);
        }
        return $selectedResources;
    }

    /**
     * Get Json Representation of Resource Tree
     *
     * @return array
     */
    public function getTree()
    {
        return $this->_integrationData->mapResources($this->getAclResources());
    }

    /**
     * Get lit of all ACL resources declared in the system.
     *
     * @return array
     * @since 2.2.0
     */
    private function getAclResources()
    {
        $resources = $this->_aclResourceProvider->getAclResources();
        $configResource = array_filter(
            $resources,
            function ($node) {
                return $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return isset($configResource['children']) ? $configResource['children'] : [];
    }
}
