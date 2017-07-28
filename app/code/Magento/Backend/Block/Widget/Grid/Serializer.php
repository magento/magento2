<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @method string|array getInputNames()
 * @since 2.0.0
 */
class Serializer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $grid = $this->getGridBlock();
        if (is_string($grid)) {
            $grid = $this->getLayout()->getBlock($grid);
        }
        if ($grid instanceof \Magento\Backend\Block\Widget\Grid) {
            $this->setGridBlock($grid)->setSerializeData($grid->{$this->getCallback()}());
        }
        return parent::_prepareLayout();
    }

    /**
     * Set serializer template
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Backend::widget/grid/serializer.phtml');
    }

    /**
     * Get grid column input names to serialize
     *
     * @param bool $asJSON
     * @return string|array
     * @since 2.0.0
     */
    public function getColumnInputNames($asJSON = false)
    {
        if ($asJSON) {
            return $this->_jsonEncoder->encode((array)$this->getInputNames());
        }
        return (array)$this->getInputNames();
    }

    /**
     * Get object data as JSON
     *
     * @return string
     * @since 2.0.0
     */
    public function getDataAsJSON()
    {
        $result = [];
        $inputNames = $this->getInputNames();
        if ($serializeData = $this->getSerializeData()) {
            $result = $serializeData;
        } elseif (!empty($inputNames)) {
            return '{}';
        }
        return $this->_jsonEncoder->encode($result);
    }
}
