<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Model\SearchEngine;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Validate search engine configuration
 */
class Validator implements ValidatorInterface
{
    /**
     * @var array
     */
    private $excludedEngineList = ['mysql' => 'MySQL'];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ValidatorInterface[] $engineValidators
     * @param array $excludedEngineList
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly array $engineValidators = [],
        array $excludedEngineList = []
    ) {
        $this->excludedEngineList = array_merge($this->excludedEngineList, $excludedEngineList);
    }

    /**
     * @inheritDoc
     */
    public function validate(): array
    {
        $errors = [];
        $currentEngine = $this->scopeConfig->getValue('catalog/search/engine');
        if (isset($this->excludedEngineList[$currentEngine])) {
            $excludedEngine = $this->excludedEngineList[$currentEngine];
            $errors[] = "Your current search engine, '{$excludedEngine}', is not supported."
                . " You must install a supported search engine before upgrading."
                . " See the System Upgrade Guide for more information.";
        }

        if (isset($this->engineValidators[$currentEngine])) {
            $validator = $this->engineValidators[$currentEngine];
            $validationErrors = $validator->validate();
            $errors = array_merge($errors, $validationErrors);
        }
        return $errors;
    }
}
