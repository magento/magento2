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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml header block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Page;

class Head extends \Magento\Page\Block\Html\Head
{
    /**
     * @var string
     */
    protected $_template = 'page/head.phtml';

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Page $page
     * @param \Magento\Core\Model\Page\Asset\MergeService $assetMergeService
     * @param \Magento\Core\Model\Page\Asset\MinifyService $assetMinifyService
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Session $session,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\App\Dir $dir,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Page $page,
        \Magento\Core\Model\Page\Asset\MergeService $assetMergeService,
        \Magento\Core\Model\Page\Asset\MinifyService $assetMinifyService,
        array $data = array()
    ) {
        $this->_session = $session;
        parent::__construct(
            $locale, $dir, $storeManager, $fileStorageDatabase, $coreData, $context, $objectManager, $page,
            $assetMergeService, $assetMinifyService, $data
        );
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->_session->getFormKey();
    }
}
