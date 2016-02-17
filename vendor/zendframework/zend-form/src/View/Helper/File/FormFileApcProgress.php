<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\File;

/**
 * A view helper to render the hidden input with a Session progress id
 * for file uploads progress tracking.
 */
class FormFileApcProgress extends FormFileUploadProgress
{
    /**
     * @return string
     */
    protected function getName()
    {
        return ini_get('apc.rfc1867_name');
    }
}
