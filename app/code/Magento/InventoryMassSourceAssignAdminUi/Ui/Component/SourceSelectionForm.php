<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignAdminUi\Ui\Component;

use Magento\Ui\Component\Form;

class SourceSelectionForm extends Form
{
    public function getDataSourceData()
    {
        return ['data' => ['general' => []]];
    }
}
