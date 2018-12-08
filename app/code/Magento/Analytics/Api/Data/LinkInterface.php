<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api\Data;

/**
<<<<<<< HEAD
 * Interface LinkInterface
 *
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 * Represents link with collected data and initialized vector for decryption.
 */
interface LinkInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getInitializationVector();
}
