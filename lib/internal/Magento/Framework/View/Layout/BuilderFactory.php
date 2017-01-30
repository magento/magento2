<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View;

/**
 * Class BuilderFactory
 */
class BuilderFactory
{
    /**#@+
     * Allowed builder types
     */
    const TYPE_LAYOUT = 'layout';
    const TYPE_PAGE   = 'page';
    /**#@-*/

    /**
     * Map of types which are references to classes
     *
     * @var array
     */
    protected $typeMap = [
        self::TYPE_LAYOUT => 'Magento\Framework\View\Layout\Builder',
        self::TYPE_PAGE   => 'Magento\Framework\View\Page\Builder',
    ];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $typeMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $typeMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->mergeTypes($typeMap);
    }

    /**
     * Add or override builder types
     *
     * @param array $typeMap
     * @return void
     */
    protected function mergeTypes(array $typeMap)
    {
        foreach ($typeMap as $typeInfo) {
            if (isset($typeInfo['type']) && isset($typeInfo['class'])) {
                $this->typeMap[$typeInfo['type']] = $typeInfo['class'];
            }
        }
    }

    /**
     * Create builder instance
     *
     * @param string $type
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return BuilderInterface
     */
    public function create($type, array $arguments)
    {
        if (empty($this->typeMap[$type])) {
            throw new \InvalidArgumentException('"' . $type . ': isn\'t allowed');
        }

        $builderInstance = $this->objectManager->create($this->typeMap[$type], $arguments);
        if (!$builderInstance instanceof BuilderInterface) {
            throw new \InvalidArgumentException(get_class($builderInstance) . ' isn\'t instance of BuilderInterface');
        }
        return $builderInstance;
    }
}
