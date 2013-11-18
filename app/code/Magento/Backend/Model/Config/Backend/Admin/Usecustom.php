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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Config backend model for "Use Custom Admin URL" option
 */
namespace Magento\Backend\Model\Config\Backend\Admin;

class Usecustom extends \Magento\Core\Model\Config\Value
{
    /**
     * Writer of configuration storage
     *
     * @var \Magento\Core\Model\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Config\Storage\WriterInterface $configWriter,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_configWriter = $configWriter;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Validate custom url
     *
     * @return \Magento\Backend\Model\Config\Backend\Admin\Usecustom
     * @throws \Magento\Core\Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if ($value == 1) {
            $customUrl = $this->getData('groups/url/fields/custom/value');
            if (empty($customUrl)) {
                throw new \Magento\Core\Exception(
                    __('Please specify the admin custom URL.')
                );
            }
        }

        return $this;
    }

    /**
     * Delete custom admin url from configuration if "Use Custom Admin Url" option disabled
     *
     * @return \Magento\Backend\Model\Config\Backend\Admin\Usecustom
     */
    protected function _afterSave()
    {
        $value = $this->getValue();

        if (!$value) {
            $this->_configWriter->delete(
                \Magento\Backend\Model\Config\Backend\Admin\Custom::XML_PATH_SECURE_BASE_URL,
                \Magento\Backend\Model\Config\Backend\Admin\Custom::CONFIG_SCOPE,
                \Magento\Backend\Model\Config\Backend\Admin\Custom::CONFIG_SCOPE_ID
            );
            $this->_configWriter->delete(
                \Magento\Backend\Model\Config\Backend\Admin\Custom::XML_PATH_UNSECURE_BASE_URL,
                \Magento\Backend\Model\Config\Backend\Admin\Custom::CONFIG_SCOPE,
                \Magento\Backend\Model\Config\Backend\Admin\Custom::CONFIG_SCOPE_ID
            );
        }

        return $this;
    }
}
