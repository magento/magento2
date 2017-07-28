<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\Installer;
use Magento\Setup\Model\RequestDataConverter;
use Magento\Setup\Validator\AdminCredentialsValidator;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Controller for admin credentials validation
 * @since 2.1.0
 */
class ValidateAdminCredentials extends AbstractActionController
{
    /**
     * @var AdminCredentialsValidator
     * @since 2.1.0
     */
    private $adminCredentialsValidator;

    /**
     * @var RequestDataConverter
     * @since 2.1.0
     */
    private $requestDataConverter;

    /**
     * Initialize dependencies.
     *
     * @param AdminCredentialsValidator $adminCredentialsValidator
     * @param RequestDataConverter $requestDataConverter
     * @since 2.1.0
     */
    public function __construct(
        AdminCredentialsValidator $adminCredentialsValidator,
        RequestDataConverter $requestDataConverter
    ) {
        $this->adminCredentialsValidator = $adminCredentialsValidator;
        $this->requestDataConverter = $requestDataConverter;
    }

    /**
     * Validate admin credentials.
     *
     * @return JsonModel
     * @since 2.1.0
     */
    public function indexAction()
    {
        try {
            $content = $this->getRequest()->getContent();
            $source = $content ? $source = Json::decode($content, Json::TYPE_ARRAY) : [];
            $data = $this->requestDataConverter->convert($source);
            $this->adminCredentialsValidator->validate($data);
            return new JsonModel(['success' => true]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
