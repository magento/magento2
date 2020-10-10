<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Form
     */
    private $form;
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $decoder;

    public function __construct(
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Customer\Block\Adminhtml\Edit\Form $form,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->form = $form;
        $this->decoder = $decoder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($fromPath=$this->decoder->decode($this->form->getRequest()->getParam('fromPath'))) {
            return $this->getUrl(
                $fromPath,
                ['id' => $this->form->getRequest()->getParam('review_id')]
            );
        }
        return $this->getUrl('*/*/');
    }
}
