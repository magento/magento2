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

namespace Mtf\EntryPoint;

use Mtf\ObjectManager;

/**
 * Class EntryPoint
 * Application entry point, used to bootstrap and run application
 *
 */
class EntryPoint
{
    /**
     * @var string
     */
    protected $_rootDir;

    /**
     * @var array
     */
    protected $_parameters;

    /**
     * Application object manager
     *
     * @var ObjectManager
     */
    protected $_locator;

    /**
     * @param string $rootDir
     * @param array $parameters
     * @param ObjectManager $objectManager
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function __construct(
        $rootDir,
        array $parameters = array(),
        ObjectManager $objectManager = null
    ) {
        $this->_rootDir = $rootDir;
        $this->_parameters = $parameters;
        $this->_locator = $objectManager;
    }

    /**
     * Run a Mtf application
     *
     * @param $applicationName
     * @param array $arguments
     * @return mixed
     * @throws \DomainException
     */
    public function run($applicationName, array $arguments = array())
    {
        try {
            if (!$this->_locator) {
                $locatorFactory = new \Mtf\ObjectManagerFactory();
                $this->_locator = $locatorFactory->create();
            }
            return $this->_locator->create($applicationName, $arguments)->launch();
        } catch (\Exception $exception) {
            $message = "Error happened during application run.\n";
            $message .= $exception->getMessage();
            throw new \DomainException($message);
        }
    }
}
