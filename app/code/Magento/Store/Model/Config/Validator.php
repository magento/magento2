<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ValidatorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Validates that scopes data contain correct values
 * @since 2.2.0
 */
class Validator implements ValidatorInterface
{
    /**
     * Checks that scopes data contain at least one not admin website, group and store
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function validate(array $data)
    {
        $errorMessage = ['Scopes data should have at least one not admin website, group and store.'];
        //list of scope names and their identifier for admin scopes in $data.
        $entities = [
            ScopeInterface::SCOPE_GROUPS => 0,
            ScopeInterface::SCOPE_STORES => 'admin',
            ScopeInterface::SCOPE_WEBSITES => 'admin'
        ];
        foreach ($entities as $scopeName => $key) {
            if (empty($data[$scopeName])
                || (count($data[$scopeName]) == 1 && isset($data[$scopeName][$key]))) {
                return $errorMessage;
            }
        }
        return [];
    }
}
