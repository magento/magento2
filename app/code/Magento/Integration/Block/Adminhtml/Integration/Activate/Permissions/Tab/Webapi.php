<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Block\Adminhtml\Integration\Activate\Permissions\Tab;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * API permissions tab for integration activation dialog.
 *
 * @api
 * @since 100.0.2
 */
class Webapi extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string[]
     */
    protected $_selectedResources;

    /**
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResource;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface
     */
    protected $_resourceProvider;

    /**
     * @var \Magento\Integration\Helper\Data
     */
    protected $_integrationData;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $encoder;

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Framework\Json\Encoder $encoder
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Json\Encoder $encoder,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        array $data = []
    ) {
        $this->_rootResource = $rootResource;
        $this->_resourceProvider = $resourceProvider;
        $this->_integrationData = $integrationData;
        $this->encoder = $encoder;
        $this->integrationService = $integrationService;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set the selected resources, which is an array of resource ids.
     *
     * If everything is allowed, the array will contain just the root resource id, which is "Magento_Backend::all".
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if (is_array($integrationData)
            && isset($integrationData['integration_id'])
            && $integrationData['integration_id']
        ) {
            $this->_selectedResources = $this->integrationService->getSelectedResources(
                $integrationData['integration_id']
            );
        } else {
            $this->_selectedResources = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        return isset(
            $integrationData[Info::DATA_SETUP_TYPE]
        ) && $integrationData[Info::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG;
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('API');
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('API');
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check if everything is allowed.
     *
     * @return bool
     */
    public function isEverythingAllowed()
    {
        return in_array($this->_rootResource->getId(), $this->_selectedResources);
    }

    /**
     * Get requested permissions tree.
     *
     * @return string
     */
    public function getResourcesTreeJson()
    {
        $aclResourcesTree = $this->_integrationData->mapResources(
            $this->getAclResources(),
            $this->getSelectedResources()
        );

        return $this->encoder->encode($this->disableAclTreeNodes($aclResourcesTree));
    }

    /**
     * Mark tree nodes as disabled
     *
     * @param array $aclResourcesTree
     * @return array
     */
    private function disableAclTreeNodes(array $aclResourcesTree)
    {
        $output = [];
        foreach ($aclResourcesTree as $node) {
            if (!isset($node['state']['selected']) || $node['state']['selected'] !== true) {
                continue;
            }
            $node['state']['disabled'] = true;
            if (isset($node['children'])) {
                $node['children'] = $this->disableAclTreeNodes($node['children']);
            }
            $output[] = $node;
        }
        return $output;
    }

    /**
     * Return json encoded array of selected resource ids.
     *
     * If everything is allowed then iterate through all
     * available resources to generate a comprehensive array of all resource ids, rather than just
     * returning "Magento_Backend::all".
     *
     * @return string
     */
    public function getSelectedResourcesJson()
    {
        return $this->encoder->encode($this->getSelectedResources());
    }

    /**
     * Return an array of selected resource ids.
     *
     * @return string[]
     */
    private function getSelectedResources()
    {
        $selectedResources = $this->_selectedResources;
        if ($this->isEverythingAllowed()) {
            $selectedResources = $this->_getAllResourceIds($this->getAclResources());
        }
        return $selectedResources;
    }

    /**
     * Get lit of all ACL resources declared in the system.
     *
     * @return array
     */
    private function getAclResources()
    {
        $resources = $this->_resourceProvider->getAclResources();
        $configResource = array_filter(
            $resources,
            function ($node) {
                return $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return isset($configResource['children']) ? $configResource['children'] : [];
    }

    /**
     * Whether tree has any resources.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isTreeEmpty()
    {
        return $this->_selectedResources === [];
    }

    /**
     * Return an array of all resource Ids.
     *
     * @param array $resources
     * @return string[]
     */
    protected function _getAllResourceIds(array $resources)
    {
        $resourceIds = [];
        foreach ($resources as $resource) {
            $resourceIds[] = [$resource['id']];
            if (isset($resource['children'])) {
                $resourceIds[] = $this->_getAllResourceIds($resource['children']);
            }
        }
        return array_merge([], ...$resourceIds);
    }
}
