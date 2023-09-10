<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * EAV Entity Attribute Data Factory
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AttributeDataFactory implements ResetAfterRequestInterface
{
    public const OUTPUT_FORMAT_JSON = 'json';
    public const OUTPUT_FORMAT_TEXT = 'text';
    public const OUTPUT_FORMAT_HTML = 'html';
    public const OUTPUT_FORMAT_PDF = 'pdf';
    public const OUTPUT_FORMAT_ONELINE = 'oneline';
    public const OUTPUT_FORMAT_ARRAY = 'array';

    // available only for multiply attributes
    /**
     * @var array
     */
    protected $_dataModels = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
        $this->_objectManager = $objectManager;
        $this->string = $string;
    }

    /**
     * Return attribute data model by attribute
     *
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_dataModels = [];
    }
}
