<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\UpdaterTaskCreator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

/**
 * Controller for updater tasks
 * @since 2.0.0
 */
class StartUpdater extends AbstractActionController
{
    /**
     * @var \Magento\Setup\Model\UpdaterTaskCreator
     * @since 2.1.0
     */
    private $updaterTaskCreator;

    /**
     * @var \Magento\Setup\Model\PayloadValidator
     * @since 2.1.0
     */
    private $payloadValidator;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Model\UpdaterTaskCreator $updaterTaskCreator
     * @param \Magento\Setup\Model\PayloadValidator $payloadValidator
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Setup\Model\UpdaterTaskCreator $updaterTaskCreator,
        \Magento\Setup\Model\PayloadValidator $payloadValidator
    ) {
        $this->updaterTaskCreator = $updaterTaskCreator;
        $this->payloadValidator = $payloadValidator;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Update action
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function updateAction()
    {
        $postPayload = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $errorMessage = '';
        if (isset($postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES])
            && is_array($postPayload[UpdaterTaskCreator::KEY_POST_PACKAGES])
            && isset($postPayload[UpdaterTaskCreator::KEY_POST_JOB_TYPE])
        ) {
            $errorMessage .= $this->payloadValidator->validatePayload($postPayload);
            if (empty($errorMessage)) {
                $errorMessage = $this->updaterTaskCreator->createUpdaterTasks($postPayload);
            }
        } else {
            $errorMessage .= 'Invalid request';
        }
        $success = empty($errorMessage);
        return new JsonModel(['success' => $success, 'message' => $errorMessage]);
    }
}
