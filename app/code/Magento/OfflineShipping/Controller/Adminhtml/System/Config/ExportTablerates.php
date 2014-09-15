<?php
/**
 *
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
namespace Magento\OfflineShipping\Controller\Adminhtml\System\Config;

use \Magento\Framework\App\ResponseInterface;
use Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker;

class ExportTablerates extends \Magento\Backend\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'tablerates.csv';
        /** @var $gridBlock \Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid */
        $gridBlock = $this->_view->getLayout()->createBlock(
            'Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid'
        );
        $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));
        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/tablerate/condition_name');
        }
        $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);
        $content = $gridBlock->getCsvFile();
        return $this->_fileFactory->create($fileName, $content, \Magento\Framework\App\Filesystem::VAR_DIR);
    }
}
