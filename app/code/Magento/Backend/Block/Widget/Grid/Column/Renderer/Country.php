<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Country column type renderer
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class Country extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     * @since 2.0.0
     */
    protected $localeLists;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeLists = $localeLists;
    }

    /**
     * Render country grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $name = $this->localeLists->getCountryTranslation($data);
            if (empty($name)) {
                $name = $this->escapeHtml($data);
            }
            return $name;
        }
        return null;
    }
}
