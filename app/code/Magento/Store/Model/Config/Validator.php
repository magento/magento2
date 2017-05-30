<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Framework\App\DeploymentConfig\ValidatorInterface;

/**
 * Imports stores, websites and groups from transmitted data.
 */
class Validator implements ValidatorInterface
{
    /**
     * Checks that scopes data contain at least one not admin website, group and store
     *
     * {@inheritdoc}
     */
    public function validate (array $data)
    {
        $errorMessage = ['Scopes data should have at least one not admin website, group and store.'];
        //list of scope names and their identifier for admin scopes in $data.
        $entities = [
            ScopeInterface::SCOPE_GROUPS => 0,
            ScopeInterface::SCOPE_STORES => 'admin',
            ScopeInterface::SCOPE_WEBSITES => 'admin'
        ];
        foreach ($entities as $scopeName => $key) {
            if (empty($data[$scopeName])) {
                return $errorMessage;
            } elseif (count($data[$scopeName]) == 1 && isset($data[$scopeName][$key])) {
                return $errorMessage;
            }
        }
        return [];
    }
}