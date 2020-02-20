<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Status\Grid\Column;

use Magento\Framework\App\ObjectManager;
use \Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @api
 * @since 100.0.2
 */
class Unassign extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritDoc
     *
     * @param Json|null $json
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?Json $json = null
    ) {
        parent::__construct($context, $data);
        $this->json = $json ?? ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Add decorated action to column
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateAction'];
    }

    /**
     * Decorate values to column
     *
     * @param string $value
     * @param \Magento\Sales\Model\Order\Status $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl('*/*/unassign');
            $label = __('Unassign');
            $cell = '<a href="#" data-post="'
                .$this->escapeHtmlAttr(
                    $this->json->serialize([
                        'action' => $url,
                        'data' => ['status' => $row->getStatus(), 'state' => $row->getState()]
                    ])
                )
                .'">' . $label . '</a>';
        }
        return $cell;
    }
}
