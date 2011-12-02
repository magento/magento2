/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/**
 * Convert a single file-input element into a 'multiple' input list
 *
 * Usage:
 *
 *   1. Create a file input element (no name)
 *      eg. <input type="file" id="first_file_element">
 *
 *   2. Create a DIV for the output to be written to
 *      eg. <div id="files_list"></div>
 *
 *   3. Instantiate a MultiSelector object, passing in the DIV and an (optional) maximum number of files
 *      eg. var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 3 );
 *
 *   4. Add the first element
 *      eg. multi_selector.addElement( document.getElementById( 'first_file_element' ) );
 *
 *   5. That's it.
 *
 *   You might (will) want to play around with the addListRow() method to make the output prettier.
 *
 *   You might also want to change the line
 *       element.name = 'file_' + this.count;
 *   ...to a naming convention that makes more sense to you.
 *
 */
function MultiSelector( list_target, field_name, max, new_element_html, delete_text, new_file_input ){

    // Where to write the list
    this.list_target = list_target;
    // Field name
    this.field_name = field_name;
    // How many elements?
    this.count = 0;
    // How many elements?
    this.id = 0;
    // Is there a maximum?
    if( max ){
        this.max = max;
    } else {
        this.max = -1;
    };
    this.new_element_html = new_element_html;
    this.delete_text = delete_text;
    this.new_file_input = new_file_input;

    /**
     * Add a new file input element
     */
    this.addElement = function( element ){

        // Make sure it's a file input element
        if( element.tagName == 'INPUT' && element.type == 'file' ){

            // Element name -- what number am I?
            // element.name = 'file_' + this.id++;
            this.id++;
            element.name = this.field_name + '[]';

            // Add reference to this object
            element.multi_selector = this;

            // What to do when a file is selected
            element.onchange = function(){

                // New file input
                var new_element = document.createElement( 'input' );
                new_element.type = 'file';

                // Add new element
                this.parentNode.insertBefore( new_element, this );

                // Apply 'update' to element
                this.multi_selector.addElement( new_element );

                // Update list
                this.multi_selector.addListRow( this );

                // Hide this: we can't use display:none because Safari doesn't like it
                this.style.position = 'absolute';
                this.style.left = '-1000px';

            };
            // If we've reached maximum number, disable input element
            if( this.max != -1 && this.count >= this.max ){
                element.disabled = true;
            };

            // File element counter
            this.count++;
            // Most recent element
            this.current_element = element;

        } else {
            // This can only be applied to file input elements!
            alert( 'Error: not a file input element' );
        };

    };

    /**
     * Add a new row to the list of files
     */
    this.addListRow = function( element ){

/*
        // Row div
        var new_row = document.createElement( 'div' );
*/

        // Sort order input
        var new_row_input = document.createElement( 'input' );
        new_row_input.type = 'text';
        new_row_input.name = 'general[position_new][]';
        new_row_input.size = '3';
        new_row_input.value = '0';

        // Delete button
        var new_row_button = document.createElement( 'input' );
        new_row_button.type = 'checkbox';
        new_row_button.value = 'Delete';

        var new_row_span = document.createElement( 'span' );
        new_row_span.innerHTML = this.delete_text;

        table = this.list_target;

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

//            if (j==0) {
//                newCell.innerHTML = this.new_element_html.replace(/%file%/g, element.value).replace(/%id%/g, this.id).replace(/%j%/g, j)
//                    + this.new_file_input.replace(/%file%/g, element.value).replace(/%id%/g, this.id).replace(/%j%/g, j);
//            }
            if (j==3) {
                newCell.appendChild( new_row_input );
            }
            else if (j==4) {
                newCell.appendChild( new_row_button );
            }
            else {
//                newCell.innerHTML = this.new_file_input.replace(/%file%/g, element.value).replace(/%id%/g, this.id).replace(/%j%/g, j);
                newCell.innerHTML = this.new_file_input.replace(/%id%/g, this.id).replace(/%j%/g, j);
            }

//            newCell.innerHTML="NEW CELL" + j;

        }

        // References
//		new_row.element = element;

        // Delete function
        new_row_button.onclick= function(){

            // Remove element from form
            this.parentNode.element.parentNode.removeChild( this.parentNode.element );

            // Remove this row from the list
            this.parentNode.parentNode.removeChild( this.parentNode );

            // Decrement counter
            this.parentNode.element.multi_selector.count--;

            // Re-enable input element (if it's disabled)
            this.parentNode.element.multi_selector.current_element.disabled = false;

            // Appease Safari
            //    without it Safari wants to reload the browser window
            //    which nixes your already queued uploads
            return false;
        };

        // Set row value
//		new_row.innerHTML = this.new_element_html.replace(/%file%/g, element.value).replace(/%id%/g, this.id);

        // Add button
//		new_row.appendChild( new_row_button );
//		new_row.appendChild( new_row_span );

        // Add it to the list
//		this.list_target.appendChild( new_row );

    };

    // Insert row into table.
    this.insRowLast = function ( table ){

        // noOfRpws in table.
        noOfRows = table.rows.length;
        // no of columns of last row.
        noOfCols = table.rows[noOfRows-1].cells.length;

        // insert row at last.
        var x=table.insertRow(noOfRows);

        // insert cells in row.
        for (var j = 0; j < noOfCols; j++) {
            newCell = x.insertCell(j);
            newCell.innerHTML="NEW CELL" + j;
        }

    };

    //delete row
    this.deleteRow = function ( table, row ){

        table.deleteRow(row);

    };

    //delete last row
    this.deleteRow = function ( table ){

        noOfRows = table.rows.length;
        table.deleteRow(noOfRows-1);

    };


};
