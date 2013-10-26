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
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable sample model
 *
 * @method \Magento\Downloadable\Model\Resource\Sample _getResource()
 * @method \Magento\Downloadable\Model\Resource\Sample getResource()
 * @method int getProductId()
 * @method \Magento\Downloadable\Model\Sample setProductId(int $value)
 * @method string getSampleUrl()
 * @method \Magento\Downloadable\Model\Sample setSampleUrl(string $value)
 * @method string getSampleFile()
 * @method \Magento\Downloadable\Model\Sample setSampleFile(string $value)
 * @method string getSampleType()
 * @method \Magento\Downloadable\Model\Sample setSampleType(string $value)
 * @method int getSortOrder()
 * @method \Magento\Downloadable\Model\Sample setSortOrder(int $value)
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Model;

class Sample extends \Magento\Core\Model\AbstractModel
{
    const XML_PATH_SAMPLES_TITLE = 'catalog/downloadable/samples_title';

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirModel;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\App\Dir $dirModel
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\App\Dir $dirModel,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_dirModel = $dirModel;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Downloadable\Model\Resource\Sample');
        parent::_construct();
    }

    /**
     * Return sample files path
     *
     * @return string
     */
    public function getSampleDir()
    {
        return $this->_dirModel->getDir();
    }

    /**
     * After save process
     *
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _afterSave()
    {
        $this->getResource()
            ->saveItemTitle($this);
        return parent::_afterSave();
    }

    /**
     * Retrieve sample URL
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->getSampleUrl()) {
            return $this->getSampleUrl();
        } else {
            return $this->getSampleFile();
        }
    }

    /**
     * Retrieve base tmp path
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return $this->_dirModel->getDir(\Magento\App\Dir::MEDIA)
            . DS . 'downloadable' . DS . 'tmp' . DS . 'samples';
    }

    /**
     * Retrieve sample files path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->_dirModel->getDir(\Magento\App\Dir::MEDIA)
            . DS . 'downloadable' . DS . 'files' . DS . 'samples';
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        return $this->_getResource()
            ->getSearchableData($productId, $storeId);
    }
}
