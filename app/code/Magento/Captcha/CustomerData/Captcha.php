<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Captcha section
 */
class Captcha extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var array
     */
    private $formIds;

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Captcha\Helper\Data $helper
     * @param array $formIds
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        array $formIds,
        array $data = []
    ) {
        parent::__construct($data);
        $this->helper = $helper;
        $this->formIds = $formIds;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData() :array
    {
        $data = [];

        foreach ($this->formIds as $formId) {
            $captchaModel = $this->helper->getCaptcha($formId);
            $data[$formId] = [
                'isRequired' => $captchaModel->isRequired(),
                'timestamp' => time()
            ];
        }

        return $data;
    }
}
