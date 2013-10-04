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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage consumers controller
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Oauth\Controller\Adminhtml\Oauth;

class Consumer extends \Magento\Backend\Controller\AbstractAction
{
    /** Param Key for extracting consumer id from Request */
    const PARAM_CONSUMER_ID = 'id';

    /** Data keys for extracting information from Consumer data array */
    const DATA_CONSUMER_ID = 'consumer_id';
    const DATA_ENTITY_ID = 'entity_id';
    const DATA_KEY = 'key';
    const DATA_SECRET = 'secret';
    const DATA_VERIFIER = 'oauth_verifier';

    /** Keys used for registering data into the registry */
    const REGISTRY_KEY_CURRENT_CONSUMER = 'current_consumer';

    /** Key use for storing/retrieving consumer data in/from the session */
    const SESSION_KEY_CONSUMER_DATA = 'consumer_data';

    /** @var \Magento\Core\Model\Registry  */
    private $_registry;

    /** @var \Magento\Oauth\Model\Consumer\Factory */
    private $_consumerFactory;

    /** @var \Magento\Oauth\Service\OauthV1Interface */
    private $_oauthService;

    /** @var \Magento\Oauth\Helper\Service */
    protected $_oauthHelper;

    /** @var \Magento\Core\Model\Logger */
    protected $_logger;

    /**
     * Class constructor
     *
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Oauth\Helper\Service $oauthHelper
     * @param \Magento\Oauth\Model\Consumer\Factory $consumerFactory
     * @param \Magento\Oauth\Service\OauthV1Interface $oauthService
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Backend\Controller\Context $context
     * @param string $areaCode
     */
    public function __construct(
        \Magento\Core\Model\Registry $registry,
        \Magento\Oauth\Helper\Service $oauthHelper,
        \Magento\Oauth\Model\Consumer\Factory $consumerFactory,
        \Magento\Oauth\Service\OauthV1Interface $oauthService,
        \Magento\Core\Model\Logger $logger,
        \Magento\Backend\Controller\Context $context,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_registry = $registry;
        $this->_oauthHelper = $oauthHelper;
        $this->_consumerFactory = $consumerFactory;
        $this->_oauthService = $oauthService;
        $this->_logger = $logger;
    }

    /**
     * Perform layout initialization actions
     *
     * @return \Magento\Oauth\Controller\Adminhtml\Oauth\Consumer
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('Magento_Oauth::system_oauth_consumer');
        return $this;
    }

    /**
     * Unset unused data from request
     * Skip getting "key" and "secret" because its generated from server side only
     *
     * @param array $data
     * @return array
     */
    protected function _filter(array $data)
    {
        foreach (array(self::PARAM_CONSUMER_ID, self::DATA_KEY, self::DATA_SECRET, 'back', 'form_key') as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Retrieve the consumer.
     *
     * @param int $consumerId - The ID of the consumer
     * @return \Magento\Oauth\Model\Consumer
     */
    protected function _fetchConsumer($consumerId)
    {
        $consumer = $this->_consumerFactory->create();

        if (!$consumerId) {
            $this->_getSession()->addError(__('Invalid ID parameter.'));
            $this->_redirect('*/*/index');
            return $consumer;
        }

        $consumer = $consumer->load($consumerId);

        if (!$consumer->getId()) {
            $this->_getSession()
                ->addError(__('An add-on with ID %1 was not found.', $consumerId));
            $this->_redirect('*/*/index');
        }

        return $consumer;
    }

    /**
     * Init titles
     *
     * @return \Magento\Oauth\Controller\Adminhtml\Oauth\Consumer
     */
    public function preDispatch()
    {
        $this->_title(__('Add-Ons'));
        parent::preDispatch();
        return $this;
    }

    /**
     * Render grid page
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Render grid AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new consumer action
     */
    public function newAction()
    {
        $consumer = $this->_consumerFactory->create();

        $formData = $this->_getFormData();
        if ($formData) {
            $this->_setFormData($formData);
            $consumer->addData($formData);
        } else {
            $consumer->setData(self::DATA_KEY, $this->_oauthHelper->generateConsumerKey());
            $consumer->setData(self::DATA_SECRET, $this->_oauthHelper->generateConsumerSecret());
            $this->_setFormData($consumer->getData());
        }

        $this->_registry->register(self::REGISTRY_KEY_CURRENT_CONSUMER, $consumer->getData());

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Edit consumer action
     */
    public function editAction()
    {
        $consumerId = (int)$this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        $consumer = $this->_fetchConsumer($consumerId);

        $consumer->addData($this->_filter($this->getRequest()->getParams()));
        $this->_registry->register(self::REGISTRY_KEY_CURRENT_CONSUMER, $consumer->getData());

        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * Redirect either to edit an existing consumer or to add a new consumer.
     *
     * @param int|null $consumerId - A consumer id.
     */
    private function _redirectToEditOrNew($consumerId)
    {
        if ($consumerId) {
            $this->_redirect('*/*/edit', array(self::PARAM_CONSUMER_ID => $consumerId));
        } else {
            $this->_redirect('*/*/new');
        }
    }

    /**
     * Save consumer action
     */
    public function saveAction()
    {
        $consumerId = $this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        if (!$this->_validateFormKey()) {
            $this->_redirectToEditOrNew($consumerId);
            return;
        }

        $data = $this->_filter($this->getRequest()->getParams());

        if ($consumerId) {
            $data = array_merge($this->_fetchConsumer($consumerId)->getData(), $data);
        } else {
            $dataForm = $this->_getFormData();
            if ($dataForm) {
                $data[self::DATA_KEY] = $dataForm[self::DATA_KEY];
                $data[self::DATA_SECRET] = $dataForm[self::DATA_SECRET];
            } else {
                // If an admin started to create a new consumer and at this moment he has been edited an existing
                // consumer, we save the new consumer with a new key-secret pair
                $data[self::DATA_KEY] = $this->_oauthHelper->generateConsumerKey();
                $data[self::DATA_SECRET] = $this->_oauthHelper->generateConsumerSecret();
            }
        }

        $verifier = array();
        try {
            $consumerData = $this->_oauthService->createConsumer($data);
            $consumerId = $consumerData[self::DATA_ENTITY_ID];
            $verifier = $this->_oauthService->postToConsumer(array(self::DATA_CONSUMER_ID => $consumerId));
            $this->_getSession()->addSuccess(__('The add-on has been saved.'));
            $this->_setFormData(null);
        } catch (\Magento\Core\Exception $e) {
            $this->_setFormData($data);
            $this->_getSession()->addError($this->_oauthHelper->escapeHtml($e->getMessage()));
            $this->getRequest()->setParam('back', 'edit');
        } catch (\Exception $e) {
            $this->_setFormData(null);
            $this->_logger->logException($e);
            $this->_getSession()->addError(__('An error occurred on saving consumer data.'));
        }

        if ($this->getRequest()->getParam('back')) {
            $this->_redirectToEditOrNew($consumerId);
        } else if ($verifier[self::DATA_VERIFIER]) {
            /** TODO: Complete when we have the Add-On website URL */
            //$this->_redirect('<Add-On Website URL>', array(
                    //'oauth_consumer_key' => $consumerData[self::DATA_KEY],
                    //'oauth_verifier' => $verifier[self::DATA_VERIFIER],
                    //'callback_url' => $this->getUrl('*/*/index')
                //));
            $this->_redirect('*/*/index');
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = $this->getRequest()->getActionName();
        $resourceId = null;

        switch ($action) {
            case 'delete':
                $resourceId = 'Magento_Oauth::consumer_delete';
                break;
            case 'new':
            case 'save':
                $resourceId = 'Magento_Oauth::consumer_edit';
                break;
            default:
                $resourceId = 'Magento_Oauth::consumer';
                break;
        }

        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form data
     *
     * @return array
     */
    protected function _getFormData()
    {
        return $this->_getSession()->getData(self::SESSION_KEY_CONSUMER_DATA, true);
    }

    /**
     * Set form data
     *
     * @param $data
     * @return \Magento\Oauth\Controller\Adminhtml\Oauth\Consumer
     */
    protected function _setFormData($data)
    {
        $this->_getSession()->setData(self::SESSION_KEY_CONSUMER_DATA, $data);
        return $this;
    }

    /**
     * Delete consumer action
     */
    public function deleteAction()
    {
        $consumerId = (int) $this->getRequest()->getParam(self::PARAM_CONSUMER_ID);
        if ($consumerId) {
            try {
                $this->_fetchConsumer($consumerId)->delete();
                $this->_getSession()->addSuccess(__('The add-on has been deleted.'));
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()
                    ->addException($e, __('An error occurred while deleting the add-on.'));
            }
        }
        $this->_redirect('*/*/index');
    }
}
