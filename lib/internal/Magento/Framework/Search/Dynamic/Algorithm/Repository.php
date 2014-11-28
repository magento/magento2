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
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Model\Exception;

class Repository
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $algorithms = [];

    /**
     * @var AlgorithmInterface[]
     */
    private $instances = [];

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $algorithms
     */
    public function __construct(ObjectManagerInterface $objectManager, array $algorithms)
    {
        $this->objectManager = $objectManager;
        $this->algorithms = $algorithms;
    }

    /**
     * Create algorithm
     *
     * @param string $algorithmType
     * @param array $data
     * @throws Exception
     * @return AlgorithmInterface
     */
    public function get($algorithmType, array $data = [])
    {
        if (!isset($this->instances[$algorithmType])) {
            if (!isset($this->algorithms[$algorithmType])) {
                throw new Exception($algorithmType . ' was not found in algorithms');
            }

            $className = $this->algorithms[$algorithmType];
            $model = $this->objectManager->create($className, $data);

            if (!$model instanceof AlgorithmInterface) {
                throw new Exception(
                    $className . ' doesn\'t extends \Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface'
                );
            }
            $this->instances[$algorithmType] = $model;
        }

        return $this->instances[$algorithmType];
    }
}
