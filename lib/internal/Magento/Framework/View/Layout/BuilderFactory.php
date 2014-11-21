<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
