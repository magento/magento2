<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Block\Role\Tab;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Integration\Helper\Data as IntegrationHelper;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;

/**
 * Rolesedit Tab Display Block.
 *
 * @api
 * @since 100.0.2
 */
class Edit extends Form implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Magento_User::role/edit.phtml';

    /**
     * Root ACL Resource
     *
     * @var RootResource
     */
    protected $_rootResource;

    /**
     * @var CollectionFactory
     */
    protected $_rulesCollectionFactory;

    /**
     * Acl builder
     *
     * @var AclRetriever
     */
    protected $_aclRetriever;

    /**
     * @var ProviderInterface
     */
    protected $_aclResourceProvider;

    /**
     * @var IntegrationHelper
     */
    protected $_integrationData;

    /**
     * @var Registry
     * @since 100.1.0
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param AclRetriever $aclRetriever
     * @param RootResource $rootResource
     * @param CollectionFactory $rulesCollectionFactory
     * @param ProviderInterface $aclResourceProvider
     * @param IntegrationHelper $integrationData
     * @param array $data
     */
    public function __construct(
        Context $context,
        AclRetriever $aclRetriever,
        RootResource $rootResource,
        CollectionFactory $rulesCollectionFactory,
        ProviderInterface $aclResourceProvider,
        IntegrationHelper $integrationData,
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
     * @param Registry $coreRegistry
     * @return void
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function setCoreRegistry(Registry $coreRegistry)
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get core registry
     *
     * @return Registry
     * @deprecated 100.1.0
     * @since 100.1.0
     */
    public function getCoreRegistry()
    {
        if (!($this->coreRegistry instanceof Registry)) {
            return ObjectManager::getInstance()->get(Registry::class);
        } else {
            return $this->coreRegistry;
        }
    }

    /**
     * Get tab label
     *
     * @return Phrase
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
     * @return array|mixed|string[]
     * @since 100.1.0
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
        return $this->_integrationData->mapResources($this->getAclResources(), $this->getSelectedResources());
    }

    /**
     * Get lit of all ACL resources declared in the system.
     *
     * @return array
     */
    private function getAclResources()
    {
        $resources = $this->_aclResourceProvider->getAclResources();
        $configResource = array_filter(
            $resources,
            function ($node) {
                return isset($node['id'])
                    && $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return $configResource['children'] ?? [];
    }
}
