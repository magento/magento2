<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Control;

use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

/**
 * Login as Customer button UI component.
 */
class LoginAsCustomerButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigInterface $config
     * @param DataProvider $dataProvider
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $config,
        ?DataProvider $dataProvider = null
    ) {
        parent::__construct($context, $registry);
        $this->authorization = $context->getAuthorization();
        $this->config = $config;
        $this->dataProvider = $dataProvider ?? ObjectManager::getInstance()->get(DataProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $customerId = (int)$this->getCustomerId();
        $data = [];
        $isAllowed = $customerId && $this->authorization->isAllowed('Magento_LoginAsCustomer::login');
        $isEnabled = $this->config->isEnabled();
        if ($isAllowed && $isEnabled) {
            $data = $this->dataProvider->getData($customerId);
        }

        return $data;
    }
}
