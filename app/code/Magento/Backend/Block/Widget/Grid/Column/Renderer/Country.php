<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Country column renderer
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;


class Country extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeLists;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
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
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
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
