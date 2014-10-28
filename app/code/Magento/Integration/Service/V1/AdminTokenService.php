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

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Helper\Validator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\Token\Factory as TokenModelFactory;
use Magento\Integration\Model\Resource\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\User\Model\User as UserModel;

/**
 * Class to handle token generation for Admins
 *
 */
class AdminTokenService implements AdminTokenServiceInterface
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * User Model
     *
     * @var UserModel
     */
    private $userModel;

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
     * @param UserModel $userModel
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Helper\Validator $validatorHelper
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        UserModel $userModel,
        TokenCollectionFactory $tokenModelCollectionFactory,
        Validator $validatorHelper
    ) {
        $this->tokenModelFactory = $tokenModelFactory;
        $this->userModel = $userModel;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function createAdminAccessToken($username, $password)
    {
        $this->validatorHelper->validateCredentials($username, $password);
        try {
            $this->userModel->login($username, $password);
            if (!$this->userModel->getId()) {
                /*
                 * This message is same as one thrown in \Magento\Backend\Model\Auth to keep the behavior consistent.
                 * Constant cannot be created in Auth Model since it uses legacy translation that doesn't support it.
                 * Need to make sure that this is refactored once exception handling is updated in Auth Model.
                 */
                throw new AuthenticationException('Please correct the user name or password.');
            }
        } catch (\Magento\Backend\Model\Auth\Exception $e) {
            throw new AuthenticationException($e->getMessage(), [], $e);
        } catch (\Magento\Framework\Model\Exception $e) {
            throw new LocalizedException($e->getMessage(), [], $e);
        }
        return $this->tokenModelFactory->create()->createAdminToken($this->userModel->getId())->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAdminAccessToken($adminId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByAdminId($adminId);
        if ($tokenCollection->getSize() == 0) {
            throw new LocalizedException("This user has no tokens.");
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
