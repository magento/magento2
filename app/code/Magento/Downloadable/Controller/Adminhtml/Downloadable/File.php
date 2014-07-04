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
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

/**
 * Downloadable File upload controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class File extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_sample;

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_fileHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Model\Link $link
     * @param \Magento\Downloadable\Model\Sample $sample
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Downloadable\Model\Link $link,
        \Magento\Downloadable\Model\Sample $sample,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        $this->_link = $link;
        $this->_sample = $sample;
        $this->_fileHelper = $fileHelper;
        parent::__construct($context);
    }

    /**
     * Upload file controller action
     *
     * @return void
     */
    public function uploadAction()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = $this->_sample->getBaseTmpPath();
        } elseif ($type == 'links') {
            $tmpPath = $this->_link->getBaseTmpPath();
        } elseif ($type == 'link_samples') {
            $tmpPath = $this->_link->getBaseSampleTmpPath();
        }

        try {
            $uploader = $this->_objectManager->create('Magento\Core\Model\File\Uploader', array('fileId' => $type));

            $result = $this->_fileHelper->uploadFromTmp($tmpPath, $uploader);

            if (!$result) {
                throw new \Exception('File can not be moved from temporary folder to the destination folder.');
            }

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
            $result['path'] = str_replace('\\', '/', $result['path']);

            if (isset($result['file'])) {
                $relativePath = rtrim($tmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->_objectManager->get('Magento\Core\Helper\File\Storage\Database')->saveFile($relativePath);
            }

            $result['cookie'] = array(
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            );
        } catch (\Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }
}
