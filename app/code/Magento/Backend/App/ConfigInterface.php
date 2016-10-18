<?php
/**
 * Default application path for backend area
 *
 * @copyright {}
 */
namespace Magento\Backend\App;

/**
 * Backend config accessor
 */
interface ConfigInterface
{
    /**
     * Retrieve config value by path
     *
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return mixed
     * @api
     */
    public function getValue($path);

    /**
     * Retrieve config flag
     *
     * Path should looks like keys imploded by "/". For example scopes/stores/admin
     *
     * @param string $path
     * @return bool
     * @api
     */
    public function isSetFlag($path);
}
