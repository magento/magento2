<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Block\Adminhtml\Attribute\Edit;

/**
 * Eav Attribute Block with additional js scripts in template
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * Js template
     *
     * @var string
     */
  
    protected $_template = 'Magento_Eav::attribute/edit/js.phtml';

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
     */
    private $inputtype;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype $inputtype,
        array $data = []
    ) {
        $this->inputtype = $inputtype;
        parent::__construct($context, $data);
    }

    /**
     * @deprecated Misspelled method
     * @see getCompatibleInputTypes
     */
    public function getComaptibleInputTypes()
    {
        return $this->getCompatibleInputTypes();
    }

    /**
     * Get compatible input types.
     *
     * @return array
     */
    public function getCompatibleInputTypes()
    {
        return $this->inputtype->getVolatileInputTypes();
    }

    /**
     * Get hints on input types.
     *
     * @return array
     */
    public function getInputTypeHints()
    {
        return $this->inputtype->getInputTypeHints();
    }
}
