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
namespace Magento\Directory\Model\Currency\Import;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config
     */
    protected $_serviceConfig;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Directory\Model\Currency\Import\Config $serviceConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Directory\Model\Currency\Import\Config $serviceConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_serviceConfig = $serviceConfig;
    }

    /**
     * Create new import object
     *
     * @param string $serviceName
     * @param array $data
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return \Magento\Directory\Model\Currency\Import\ImportInterface
     */
    public function create($serviceName, array $data = array())
    {
        $serviceClass = $this->_serviceConfig->getServiceClass($serviceName);
        if (!$serviceClass) {
            throw new \InvalidArgumentException("Currency import service '{$serviceName}' is not defined.");
        }
        $serviceInstance = $this->_objectManager->create($serviceClass, $data);
        if (!$serviceInstance instanceof \Magento\Directory\Model\Currency\Import\ImportInterface) {
            throw new \UnexpectedValueException(
                "Class '{$serviceClass}' has to implement \\Magento\\Directory\\Model\\Currency\\Import\\ImportInterface."
            );
        }
        return $serviceInstance;
    }
}
