<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block before edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Block\Adminhtml\Import\Edit;

/**
 * @api
 * @since 2.0.0
 */
class Before extends \Magento\Backend\Block\Template
{
    /**
     * Basic import model
     *
     * @var \Magento\ImportExport\Model\Import
     * @since 2.0.0
     */
    protected $_importModel;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ImportExport\Model\Import $importModel,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_importModel = $importModel;
        parent::__construct($context, $data);
    }

    /**
     * Returns json-encoded entity behaviors array
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityBehaviors()
    {
        $behaviors = $this->_importModel->getEntityBehaviors();
        foreach ($behaviors as $entityCode => $behavior) {
            $behaviors[$entityCode] = $behavior['code'];
        }
        return $this->_jsonEncoder->encode($behaviors);
    }

    /**
     * Returns json-encoded entity behaviors notes array
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityBehaviorsNotes()
    {
        $behaviors = $this->_importModel->getEntityBehaviors();
        foreach ($behaviors as $entityCode => $behavior) {
            $behaviors[$entityCode] = $behavior['notes'];
        }
        return $this->_jsonEncoder->encode($behaviors);
    }

    /**
     * Return json-encoded list of existing behaviors
     *
     * @return string
     * @since 2.0.0
     */
    public function getUniqueBehaviors()
    {
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        return $this->_jsonEncoder->encode(array_keys($uniqueBehaviors));
    }
}
