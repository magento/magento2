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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Dataflow Batch abstract model
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Dataflow_Model_Batch_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Set batch data
     * automatic convert to serialize data
     *
     * @param mixed $data
     * @return Mage_Dataflow_Model_Batch_Abstract
     */
    public function setBatchData($data)
    {
        if ('"libiconv"' == ICONV_IMPL) {
            foreach ($data as &$value) {
                $value = iconv('utf-8', 'utf-8//IGNORE', $value);
            }
        }

        $this->setData('batch_data', serialize($data));

        return $this;
    }

    /**
     * Retrieve batch data
     * return unserialize data
     *
     * @return mixed
     */
    public function getBatchData()
    {
        $data = $this->_data['batch_data'];
        $data = unserialize($data);
        return $data;
    }

    /**
     * Retrieve id collection
     *
     * @param int $batchId
     * @return array
     */
    public function getIdCollection($batchId = null)
    {
        if (!is_null($batchId)) {
            $this->setBatchId($batchId);
        }
        return $this->getResource()->getIdCollection($this);
    }

    public function deleteCollection($batchId = null)
    {
        if (!is_null($batchId)) {
            $this->setBatchId($batchId);
        }
        return $this->getResource()->deleteCollection($this);
    }
}
