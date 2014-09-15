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
namespace Magento\Customer\Model\Customer\Attribute\Backend;

/**
 * Website attribute backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\Logger $logger, \Magento\Framework\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
        parent::__construct($logger);
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        if ($object->getId()) {
            return $this;
        }

        if (!$object->hasData('website_id')) {
            $object->setData('website_id', $this->_storeManager->getStore()->getWebsiteId());
        }

        return $this;
    }
}
