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
namespace Magento\Eav\Model;

/**
 * EAV Entity Attribute Data Factory
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AttributeDataFactory
{
    const OUTPUT_FORMAT_JSON = 'json';

    const OUTPUT_FORMAT_TEXT = 'text';

    const OUTPUT_FORMAT_HTML = 'html';

    const OUTPUT_FORMAT_PDF = 'pdf';

    const OUTPUT_FORMAT_ONELINE = 'oneline';

    const OUTPUT_FORMAT_ARRAY = 'array';

    // available only for multiply attributes

    /**
     * Array of attribute data models by input type
     *
     * @var array
     */
    protected $_dataModels = array();

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager, \Magento\Framework\Stdlib\String $string)
    {
        $this->_objectManager = $objectManager;
        $this->string = $string;
    }

    /**
     * Return attribute data model by attribute
     * Set entity to data model (need for work)
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\Eav\Model\Attribute\Data\AbstractData
     */
    public function create(\Magento\Eav\Model\Attribute $attribute, \Magento\Framework\Model\AbstractModel $entity)
    {
        /* @var $dataModel \Magento\Eav\Model\Attribute\Data\AbstractData */
        $dataModelClass = $attribute->getDataModel();
        if (!empty($dataModelClass)) {
            if (empty($this->_dataModels[$dataModelClass])) {
                $dataModel = $this->_objectManager->create($dataModelClass);
                $this->_dataModels[$dataModelClass] = $dataModel;
            } else {
                $dataModel = $this->_dataModels[$dataModelClass];
            }
        } else {
            if (empty($this->_dataModels[$attribute->getFrontendInput()])) {
                $dataModelClass = sprintf(
                    'Magento\Eav\Model\Attribute\Data\%s',
                    $this->string->upperCaseWords($attribute->getFrontendInput())
                );
                $dataModel = $this->_objectManager->create($dataModelClass);
                $this->_dataModels[$attribute->getFrontendInput()] = $dataModel;
            } else {
                $dataModel = $this->_dataModels[$attribute->getFrontendInput()];
            }
        }

        $dataModel->setAttribute($attribute);
        $dataModel->setEntity($entity);

        return $dataModel;
    }
}
