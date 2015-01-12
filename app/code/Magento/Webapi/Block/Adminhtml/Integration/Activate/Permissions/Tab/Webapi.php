<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Block\Adminhtml\Integration\Activate\Permissions\Tab;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Webapi\Helper\Data as WebapiHelper;

/**
 * API permissions tab for integration activation dialog.
 *
 * TODO: Fix warnings suppression
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Webapi extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /** @var string[] */
    protected $_selectedResources;

    /** @var \Magento\Framework\Acl\RootResource */
    protected $_rootResource;

    /** @var \Magento\Framework\Acl\Resource\ProviderInterface */
    protected $_resourceProvider;

    /** @var \Magento\Integration\Helper\Data */
    protected $_integrationData;

    /** @var WebapiHelper */
    protected $_webapiHelper;

    /** @var \Magento\Core\Helper\Data  */
    protected $_coreHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\Resource\ProviderInterface $resourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param \Magento\Webapi\Helper\Data $webapiData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\Acl\Resource\ProviderInterface $resourceProvider,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Webapi\Helper\Data $webapiData,
        array $data = []
    ) {
        $this->_rootResource = $rootResource;
        $this->_resourceProvider = $resourceProvider;
        $this->_integrationData = $integrationData;
        $this->_webapiHelper = $webapiData;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set the selected resources, which is an array of resource ids. If everything is allowed, the
     * array will contain just the root resource id, which is "Magento_Adminhtml::all".
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_selectedResources = $this->_webapiHelper->getSelectedResources();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $integrationData = $this->_coreRegistry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        return isset(
            $integrationData[Info::DATA_SETUP_TYPE]
        ) && $integrationData[Info::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('API');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('API');
    }

    /**
     * {@inheritdoc}
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
        $resources = $this->_resourceProvider->getAclResources();
        $aclResourcesTree = $this->_integrationData->mapResources($resources[1]['children']);

        return $this->_coreHelper->jsonEncode($aclResourcesTree);
    }

    /**
     * Return an array of selected resource ids. If everything is allowed then iterate through all
     * available resources to generate a comprehensive array of all resource ids, rather than just
     * returning "Magento_Adminhtml::all".
     *
     * @return string
     */
    public function getSelectedResourcesJson()
    {
        $selectedResources = $this->_selectedResources;
        if ($this->isEverythingAllowed()) {
            $resources = $this->_resourceProvider->getAclResources();
            $selectedResources = $this->_getAllResourceIds($resources[1]['children']);
        }
        return $this->_coreHelper->jsonEncode($selectedResources);
    }

    /**
     * Whether tree has any resources.
     *
     * @return bool
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
            $resourceIds[] = $resource['id'];
            if (isset($resource['children'])) {
                $resourceIds = array_merge($resourceIds, $this->_getAllResourceIds($resource['children']));
            }
        }
        return $resourceIds;
    }
}
