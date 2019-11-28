<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\AclResource;

use \Magento\Framework\Escaper;

class TreeBuilder
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
    * Class constructor
    *
    * @param Escaper $escaper
    */
    public function __construct(
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
    }

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
            if (isset($resource['title']) && !empty($resource['title'])) {
                $resource['title'] = $this->escaper->escapeQuote($resource['title']);
            }
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
