<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;

/**
 * Class used to check if some scope belongs to other scope
 */
class ScopeResolver
{
    /**
     * @var ScopeTreeProviderInterface
     */
    private $scopeTree;

    /**
     * @param ScopeTreeProviderInterface $scopeTree
     */
    public function __construct(ScopeTreeProviderInterface $scopeTree)
    {
        $this->scopeTree = $scopeTree;
    }

    /**
     * Check is some scope belongs to other scope
     *
     * @param string $baseScope
     * @param int $baseScopeId
     * @param string $requestedScope
     * @param int $requestedScopeId
     * @return bool
     */
    public function isBelongsToScope(
        string $baseScope,
        int $baseScopeId,
        string $requestedScope,
        int $requestedScopeId
    ) : bool {
        /* All scopes belongs to All Store Views */
        if ($baseScope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return true;
        }

        $scopeNode = $this->getScopeNode($baseScope, $baseScopeId, [$this->scopeTree->get()]);
        if (empty($scopeNode)) {
            return false;
        }

        return $this->isBelongsToScopeRecurse($requestedScope, $requestedScopeId, [$scopeNode]);
    }

    /**
     * Check is Belongs some scope to other scope (internal recurse)
     *
     * @param string $requestedScope
     * @param int $requestedScopeId
     * @param array $tree
     * @return bool
     */
    private function isBelongsToScopeRecurse(
        string $requestedScope,
        int $requestedScopeId,
        array $tree
    ) : bool {
        foreach ($tree as $node) {
            if ($this->isScopeEquals($node['scope'], $requestedScope) && (int)$node['scope_id'] === $requestedScopeId) {
                return true;
            }
            if (!empty($node['scopes'])) {
                $isBelongsToChild = $this->isBelongsToScopeRecurse(
                    $requestedScope,
                    $requestedScopeId,
                    $node['scopes']
                );
                if ($isBelongsToChild) {
                    return $isBelongsToChild;
                }
            }
        }

        return false;
    }

    /**
     * Get tree by scope
     *
     * @param string $scope
     * @param int $scopeId
     * @param array $tree
     * @return array
     */
    private function getScopeNode(string $scope, int $scopeId, array $tree): array
    {
        foreach ($tree as $node) {
            if ($this->isScopeEquals($node['scope'], $scope) && (int)$node['scope_id'] === $scopeId) {
                return $node;
            }
            if (!empty($node['scopes'])) {
                $found = $this->getScopeNode($scope, $scopeId, $node['scopes']);
                if (!empty($found)) {
                    return $found;
                }
            }
        }

        return [];
    }

    /**
     * Is scope equals with normalize names
     *
     * @param string $firstScope
     * @param string $secondScope
     * @return bool
     */
    private function isScopeEquals(string $firstScope, string $secondScope): bool
    {
        return $this->normalizeScopeName($firstScope) === $this->normalizeScopeName($secondScope);
    }

    /**
     * Normalize scope name
     *
     * @param string $scope
     * @return string
     */
    private function normalizeScopeName(string $scope): string
    {
        switch ($scope) {
            case ScopeInterface::SCOPE_STORES:
                return ScopeInterface::SCOPE_STORE;
            case ScopeInterface::SCOPE_WEBSITES:
                return ScopeInterface::SCOPE_WEBSITE;
            case ScopeInterface::SCOPE_GROUPS:
                return ScopeInterface::SCOPE_GROUP;
            default:
                return $scope;
        }
    }
}
