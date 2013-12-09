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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

/**
 * Class for handling API section within integration.
 */
class Webapi extends \Magento\Backend\Block\Widget\Form
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Root ACL Resource
     *
     * @var \Magento\Core\Model\Acl\RootResource
     */
    protected $_rootResource;

    /**
     * Rules collection factory
     *
     * @var \Magento\User\Model\Resource\Rules\CollectionFactory
     */
    protected $_rulesCollFactory;

    /**
     * Acl resource provider
     *
     * @var \Magento\Acl\Resource\ProviderInterface
     */
    protected $_aclResourceProvider;

    /** @var \Magento\Integration\Helper\Data */
    protected $_integrationData;

    /** @var \Magento\Webapi\Helper\Data */
    protected $_webapiData;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Acl\RootResource $rootResource
     * @param \Magento\User\Model\Resource\Rules\CollectionFactory $rulesCollFactory
     * @param \Magento\Acl\Resource\ProviderInterface $aclResourceProvider
     * @param \Magento\Webapi\Helper\Data $webapiData
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Acl\RootResource $rootResource,
        \Magento\User\Model\Resource\Rules\CollectionFactory $rulesCollFactory,
        \Magento\Acl\Resource\ProviderInterface $aclResourceProvider,
        \Magento\Webapi\Helper\Data $webapiData,
        \Magento\Integration\Helper\Data $integrationData,
        array $data = array()
    ) {
        $this->_rootResource = $rootResource;
        $this->_rulesCollFactory = $rulesCollFactory;
        $this->_aclResourceProvider = $aclResourceProvider;
        $this->_webapiData = $webapiData;
        $this->_integrationData = $integrationData;
        parent::__construct($context, $data);
    }

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('API');
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
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setSelectedResources($this->_webapiData->getSelectedResources());
    }

    /**
     * Check if everything is allowed
     *
     * @return boolean
     */
    public function isEverythingAllowed()
    {
        return in_array($this->_rootResource->getId(), $this->getSelectedResources());
    }

    /**
     * Get Json Representation of Resource Tree
     *
     * @return array
     */
    public function getTree()
    {
        $resources = $this->_aclResourceProvider->getAclResources();
        $rootArray = $this->_integrationData->mapResources(
            isset($resources[1]['children']) ? $resources[1]['children'] : array()
        );
        return $rootArray;
    }
}
