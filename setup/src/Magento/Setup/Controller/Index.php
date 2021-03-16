<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Magento\Framework\App\ProductMetadata;
use Magento\Setup\Model\License;

/**
 * Main controller of the Setup Wizard
 */
class Index extends AbstractActionController
{
    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @var License
     */
    private $license;

    /**
     * Index constructor.
     *
     * @param ProductMetadata $productMetadata
     * @param License $license
     */
    public function __construct(
        ProductMetadata $productMetadata,
        License $license
    ) {
        $this->productMetadata = $productMetadata;
        $this->license = $license;
    }

    /**
     * Setup index action.
     *
     * @return ViewModel
     */
    public function indexAction(): ViewModel
    {
        return new ViewModel([
            'version' => $this->productMetadata->getVersion(),
            'license' => $this->license->getContents(),
        ]);
    }
}
