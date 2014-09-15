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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Integration\Service\V1;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Helper\Validator;
use Magento\Integration\Model\Oauth\Token\Factory as TokenModelFactory;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Resource\Oauth\Token\CollectionFactory as TokenCollectionFactory;

class CustomerTokenService implements CustomerTokenServiceInterface
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * Customer Account Service
     *
     * @var CustomerAccountService
     */
    private $customerAccountService;

    /**
     * @var \Magento\Integration\Helper\Validator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param CustomerAccountService $customerAccountService
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Helper\Validator $validatorHelper
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        CustomerAccountService $customerAccountService,
        TokenCollectionFactory $tokenModelCollectionFactory,
        Validator $validatorHelper
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerAccountService = $customerAccountService;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerAccessToken($username, $password)
    {
        $this->validatorHelper->validateCredentials($username, $password);
        $customerDataObject = $this->customerAccountService->authenticate($username, $password);
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeCustomerAccessToken($customerId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByCustomerId($customerId);
        if ($tokenCollection->getSize() == 0) {
            throw new LocalizedException("This customer has no tokens.");
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->setRevoked(1)->save();
            }
        } catch (\Exception $e) {
            throw new LocalizedException("The tokens could not be revoked.");
        }
        return true;
    }
}
