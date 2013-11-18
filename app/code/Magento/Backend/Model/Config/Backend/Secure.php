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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Backend;

class Secure extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\Core\Model\Page\Asset\MergeService
     */
    protected $_mergeService;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Page\Asset\MergeService $mergeService
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Page\Asset\MergeService $mergeService,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_mergeService = $mergeService;
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
     * Clean compiled JS/CSS when updating configuration settings
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_mergeService->cleanMergedJsCss();
        }
    }
}
