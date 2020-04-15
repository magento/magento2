<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element\DataType\Media;

/**
 * Basic configuration for OdenDialogUrl
 */
class OpenDialogUrl
{
    private const DEFAULT_OPEN_DIALOG_URL = 'cms/wysiwyg_images/index';

    /**
     * @var string
     */
    private $openDialogUrl;

    /**
     * @param string $openDialog
     */
    public function __construct(string $openDialog = null)
    {
        $this->openDialogUrl = $openDialog ?? self::DEFAULT_OPEN_DIALOG_URL;
    }

    /**
     * Returns open dialog url for media browser
     */
    public function getOpenDialogUrl(): string
    {
        return $this->openDialogUrl;
    }
}
