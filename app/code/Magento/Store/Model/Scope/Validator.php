<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Scope;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Validator validates scope and scope code.
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($scope, $scopeCode = null)
    {
        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT && empty($scopeCode)) {
            return true;
        }

        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT && !empty($scopeCode)) {
            throw new LocalizedException(__(
                'The "%1" scope can’t include a scope code. Try again without entering a scope code.',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            ));
        }

        if (empty($scope)) {
            throw new LocalizedException(__('Enter a scope before proceeding.'));
        }

        $this->validateScopeCode($scopeCode);

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            $scopeResolver->getScope($scopeCode)->getId();
        } catch (InvalidArgumentException $e) {
            throw new LocalizedException(__('The "%1" value doesn’t exist. Enter another value.', $scope));
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The "%1" value doesn’t exist. Enter another value."', $scopeCode));
        }

        return true;
    }

    /**
     * Validate scope code
     * Throw exception if not valid.
     *
     * @param string $scopeCode
     * @return void
     * @throws LocalizedException if scope code is empty or has a wrong format
     */
    private function validateScopeCode($scopeCode)
    {
        if (empty($scopeCode)) {
            throw new LocalizedException(__('Enter a scope code before proceeding.'));
        }

        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $scopeCode)) {
            throw new LocalizedException(__(
                'The scope code can include only lowercase letters (a-z), numbers (0-9) and underscores (_). '
                . 'Also, the first character must be a letter.'
            ));
        }
    }
}
