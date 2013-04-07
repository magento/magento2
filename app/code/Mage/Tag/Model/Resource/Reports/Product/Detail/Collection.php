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
 * @category    Mage
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Report Tags Product Detail submitted grid collection
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Resource_Reports_Product_Detail_Collection
    extends Mage_Tag_Model_Resource_Reports_Product_Collection
{
    /**
     * @var Mage_Tag_Model_Tag
     */
    protected $_model;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @param Mage_Tag_Model_Tag $tagModel
     * @param Mage_Core_Controller_Request_Http $request
     * @param null $resource
     */
    public function __construct(
        Mage_Tag_Model_Tag $tagModel,
        Mage_Core_Controller_Request_Http $request,
        $resource = null)
    {
        $this->_model = $tagModel;
        $this->_request = $request;
        parent::__construct($resource);
    }

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return Mage_Tag_Model_Resource_Product_Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addTagedCount()->addProductFilter($this->getRequest()->getParam('id'))
            ->addStatusFilter($this->_model->getApprovedStatus())->addStoresVisibility()->setActiveFilter()
            ->addGroupByTag()->setRelationId();
        return $this;
    }
}
