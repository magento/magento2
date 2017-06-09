<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Robots;

/**
 * Prepares base content for robots.txt file
 *
 * @api
 */
class Data extends AbstractBlock
{
    /**
     * @var Robots
     */
    private $robots;

    /**
     * @param Context $context
     * @param Robots $robots
     * @param array $data
     */
    public function __construct(
        Context $context,
        Robots $robots,
        array $data = []
    ) {
        $this->robots = $robots;

        parent::__construct($context, $data);
    }

    /**
     * Prepare base content for robots.txt file
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->robots->getData();
    }
}
