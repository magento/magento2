<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config\Scope;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * @deprecated 100.2.0 Added in order to avoid backward incompatibility because class was moved to another directory.
 * @see \Magento\Framework\App\Scope\Validator
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
            throw new LocalizedException(new Phrase(
                'The "%1" scope can\'t include a scope code. Try again without entering a scope code.',
                [ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            ));
        }

        if (empty($scope)) {
            throw new LocalizedException(new Phrase('A scope is missing. Enter a scope and try again.'));
        }

        $this->validateScopeCode($scopeCode);

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
            $scopeResolver->getScope($scopeCode)->getId();
        } catch (InvalidArgumentException $e) {
            throw new LocalizedException(
                new Phrase('The "%1" value doesn\'t exist. Enter another value and try again.', [$scope])
            );
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(
                new Phrase('The "%1" value doesn\'t exist. Enter another value and try again.', [$scopeCode])
            );
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
            throw new LocalizedException(new Phrase('A scope code is missing. Enter a code and try again.'));
        }

        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $scopeCode)) {
            throw new LocalizedException(new Phrase(
                'The scope code can include only lowercase letters (a-z), numbers (0-9) and underscores (_). '
                . 'Also, the first character must be a letter.'
            ));
        }
    }
}
