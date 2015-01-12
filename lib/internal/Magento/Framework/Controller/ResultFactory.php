<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller;

use Magento\Framework\ObjectManagerInterface;

/**
 * Result Factory
 */
class ResultFactory
{
    /**#@+
     * Allowed result types
     */
    const TYPE_JSON     = 'json';
    const TYPE_RAW      = 'raw';
    const TYPE_REDIRECT = 'redirect';
    const TYPE_FORWARD  = 'forward';
    const TYPE_LAYOUT   = 'layout';
    const TYPE_PAGE     = 'page';
    /**#@-*/

    /**
     * Map of types which are references to classes
     *
     * @var array
     */
    protected $typeMap = [
        self::TYPE_JSON     => 'Magento\Framework\Controller\Result\JSON',
        self::TYPE_RAW      => 'Magento\Framework\Controller\Result\Raw',
        self::TYPE_REDIRECT => 'Magento\Framework\Controller\Result\Redirect',
        self::TYPE_FORWARD  => 'Magento\Framework\Controller\Result\Forward',
        self::TYPE_LAYOUT   => 'Magento\Framework\View\Result\Layout',
        self::TYPE_PAGE     => 'Magento\Framework\View\Result\Page',
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * Add or override result types
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
     * Create new page regarding its type
     *
     * @param string $type
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return ResultInterface
     */
    public function create($type, array $arguments = [])
    {
        if (empty($this->typeMap[$type])) {
            throw new \InvalidArgumentException('"' . $type . ': isn\'t allowed');
        }

        $resultInstance = $this->objectManager->create($this->typeMap[$type], $arguments);
        if (!$resultInstance instanceof ResultInterface) {
            throw new \InvalidArgumentException(get_class($resultInstance) . ' isn\'t instance of ResultInterface');
        }

        /**
         * TODO: Temporary solution, must be removed after full refactoring to the new result rendering system
         *
         * Used for knowledge how result page was created, page was created through result factory or it's default page
         * in App\View created in constructor
         */
        if ($resultInstance instanceof \Magento\Framework\View\Result\Layout) {
            // Initialization has to be in constructor of ResultPage
            $resultInstance->addDefaultHandle();
        }

        return $resultInstance;
    }
}
