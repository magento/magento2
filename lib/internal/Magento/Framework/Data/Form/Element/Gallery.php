<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Category form input image element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Gallery extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('file');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getElementHtml()
    {
        $gallery = $this->getValue();

        $html = '<table id="gallery" class="gallery" border="0" cellspacing="3" cellpadding="0">';
        $html .= '<thead id="gallery_thead" class="gallery"><tr class="gallery"><td class="gallery" valign="middle" align="center">Big Image</td><td class="gallery" valign="middle" align="center">Thumbnail</td><td class="gallery" valign="middle" align="center">Small Thumb</td><td class="gallery" valign="middle" align="center">Sort Order</td><td class="gallery" valign="middle" align="center">Delete</td></tr></thead>';
        $widgetButton = $this->getForm()->getParent()->getLayout();
        $buttonHtml = $widgetButton->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => 'Add New Image', 'onclick' => 'addNewImg()', 'class' => 'add']
        )->toHtml();

        $html .= '<tfoot class="gallery">';
        $html .= '<tr class="gallery">';
        $html .= '<td class="gallery" valign="middle" align="left" colspan="5">' . $buttonHtml . '</td>';
        $html .= '</tr>';
        $html .= '</tfoot>';

        $html .= '<tbody class="gallery">';

        $i = 0;
        if (!is_null($this->getValue())) {
            foreach ($this->getValue() as $image) {
                $i++;
                $html .= '<tr class="gallery">';
                foreach ($this->getValue()->getAttributeBackend()->getImageTypes() as $type) {
                    $url = $image->setType($type)->getSourceUrl();
                    $html .= '<td class="gallery" align="center" style="vertical-align:bottom;">';
                    $html .= '<a href="' .
                        $url .
                        '" target="_blank" onclick="imagePreview(\'' .
                        $this->getHtmlId() .
                        '_image_' .
                        $type .
                        '_' .
                        $image->getValueId() .
                        '\');return false;" ' .
                        $this->_getUiId(
                            'image-' . $image->getValueId()
                        ) .
                        '>
                    <img id="' .
                        $this->getHtmlId() .
                        '_image_' .
                        $type .
                        '_' .
                        $image->getValueId() .
                        '" src="' .
                        $url .
                        '" alt="' .
                        $image->getValue() .
                        '" height="25" align="absmiddle" class="small-image-preview"></a><br/>';
                    $html .= '<input type="file" name="' .
                        $this->getName() .
                        '_' .
                        $type .
                        '[' .
                        $image->getValueId() .
                        ']" size="1"' .
                        $this->_getUiId(
                            'file'
                        ) . ' ></td>';
                }
                $html .= '<td class="gallery" align="center" style="vertical-align:bottom;"><input type="input" name="' .
                    parent::getName() .
                    '[position][' .
                    $image->getValueId() .
                    ']" value="' .
                    $image->getPosition() .
                    '" id="' .
                    $this->getHtmlId() .
                    '_position_' .
                    $image->getValueId() .
                    '" size="3" ' .
                    $this->_getUiId(
                        'position-' . $image->getValueId()
                    ) . '/></td>';
                $html .= '<td class="gallery" align="center" style="vertical-align:bottom;"><input type="checkbox" name="' .
                    parent::getName() .
                    '[delete][' .
                    $image->getValueId() .
                    ']" value="' .
                    $image->getValueId() .
                    '" id="' .
                    $this->getHtmlId() .
                    '_delete_' .
                    $image->getValueId() .
                    '" ' .
                    $this->_getUiId(
                        'delete-button-' . $image->getValueId()
                    ) . '/></td>';
                $html .= '</tr>';
            }
        }
        if ($i == 0) {
            $html .= '<script type="text/javascript">document.getElementById("gallery_thead").style.visibility="hidden";</script>';
        }

        $html .= '</tbody></table>';

        $name = $this->getName();
        $parentName = parent::getName();

        $html .= <<<EndSCRIPT

        <script language="javascript">
        id = 0;

        function addNewImg(){

            document.getElementById("gallery_thead").style.visibility="visible";

            id--;
            new_file_input = '<input type="file" name="{$name}_%j%[%id%]" size="1" />';

		    // Sort order input
		    var new_row_input = document.createElement( 'input' );
		    new_row_input.type = 'text';
		    new_row_input.name = '{$parentName}[position]['+id+']';
		    new_row_input.size = '3';
		    new_row_input.value = '0';

		    // Delete button
		    var new_row_button = document.createElement( 'input' );
		    new_row_button.type = 'checkbox';
		    new_row_button.value = 'Delete';

            table = document.getElementById( "gallery" );

            // no of rows in the table:
            noOfRows = table.rows.length;

            // no of columns in the pre-last row:
            noOfCols = table.rows[noOfRows-2].cells.length;

            // insert row at pre-last:
            var x=table.insertRow(noOfRows-1);

            // insert cells in row.
            for (var j = 0; j < noOfCols; j++) {

                newCell = x.insertCell(j);
                newCell.align = "center";
                newCell.valign = "middle";

                if (j==3) {
		            newCell.appendChild( new_row_input );
                }
                else if (j==4) {
		            newCell.appendChild( new_row_button );
                }
                else {
                    newCell.innerHTML = new_file_input.replace(/%j%/g, j).replace(/%id%/g, id);
                }

            }

		    // Delete function
		    new_row_button.onclick= function(){

                this.parentNode.parentNode.parentNode.removeChild( this.parentNode.parentNode );

			    // Appease Safari
			    //    without it Safari wants to reload the browser window
			    //    which nixes your already queued uploads
			    return false;
		    };

	    }
        </script>

EndSCRIPT;
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * @return mixed
     */
    public function getParentName()
    {
        return parent::getName();
    }
}
