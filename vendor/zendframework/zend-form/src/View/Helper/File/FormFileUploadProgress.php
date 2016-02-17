<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper\File;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormInput;

/**
 * A view helper to render the hidden input with a UploadProgress id
 * for file uploads progress tracking.
 */
class FormFileUploadProgress extends FormInput
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        return $this->renderHiddenId();
    }

    /**
     * Render a hidden form <input> element with the progress id
     *
     * @return string
     */
    public function renderHiddenId()
    {
        $attributes = array(
            'id'    => 'progress_key',
            'name'  => $this->getName(),
            'type'  => 'hidden',
            'value' => $this->getValue()
        );

        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        );
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return 'UPLOAD_IDENTIFIER';
    }

    /**
     * @return string
     */
    protected function getValue()
    {
        return uniqid();
    }
}
