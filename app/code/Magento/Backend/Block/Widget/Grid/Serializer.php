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
namespace Magento\Backend\Block\Widget\Grid;

/**
 * @method string|array getInputNames()
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Serializer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
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
     */
    public function getDataAsJSON()
    {
        $result = array();
        $inputNames = $this->getInputNames();
        if ($serializeData = $this->getSerializeData()) {
            $result = $serializeData;
        } elseif (!empty($inputNames)) {
            return '{}';
        }
        return $this->_jsonEncoder->encode($result);
    }
}
