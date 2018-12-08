<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
<<<<<<< HEAD
=======
 * @deprecated moved to proper namespace and extended
 * @see \Magento\Backend\Model\Image\UploadResizeConfigInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
interface UploadConfigInterface
{
    /**
<<<<<<< HEAD
     * @return int
     */
    public function getMaxWidth();

    /**
     * @return int
     */
    public function getMaxHeight();
=======
     * Get maximum image width.
     *
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * Get maximum image height.
     *
     * @return int
     */
    public function getMaxHeight(): int;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
