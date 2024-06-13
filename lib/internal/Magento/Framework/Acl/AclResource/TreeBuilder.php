<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\AclResource;

class TreeBuilder
{
    /**
     * Transform resource list into sorted resource tree that includes only active resources
     *
     * @param array $resourceList
     * @return array
     */
    public function build(array $resourceList)
    {
        $result = [];
        foreach ($resourceList as $resource) {
            if ($resource['disabled']) {
                continue;
            }
            unset($resource['disabled']);
            $resource['children'] = $this->build($resource['children']);
            $result[] = $resource;
        }
        usort($result, [$this, '_sortTree']);
        return $result;
    }

    /**
     * Sort ACL resource nodes
     *
     * @param array $nodeA
     * @param array $nodeB
     * @return int
     */
    protected function _sortTree(array $nodeA, array $nodeB)
    {
        return $nodeA['sortOrder'] < $nodeB['sortOrder'] ? -1 : ($nodeA['sortOrder'] > $nodeB['sortOrder'] ? 1 : 0);
    }
}
