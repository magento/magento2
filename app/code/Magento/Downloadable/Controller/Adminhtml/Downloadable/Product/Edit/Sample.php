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
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Downloadable\Helper\Download as DownloadHelper;

class Sample extends \Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit\Link
{
    /**
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _createLink()
    {
        return $this->_objectManager->create('Magento\Downloadable\Model\Sample');
    }

    /**
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _getLink()
    {
        return $this->_objectManager->get('Magento\Downloadable\Model\Sample');
    }

    /**
     * Download sample action
     *
     * @return void
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_createLink()->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->getFilePath(
                    $this->_getLink()->getBasePath(),
                    $sample->getSampleFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
