<?php

/**
* ------------------------------------------------------
*         Sequence Diagram Library: SeDiLib
* ------------------------------------------------------
*
* @copyright (C) 2013 Alexander Giesler (giesler.alexander@googlemail.com)
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 3 of
* the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public
* License along with this program; if not, see
* <http://www.gnu.org/licenses/>.
*/

/** Special message type for creating, splitting end closing blocks
*/
class SequenceBlockAction {
    /** Block mode
    * 'create' = Create a new block
    * 'create_empty' = Create a new block, fill with background color
    * 'split'  = Split
    * 'close'  = Close
    */
    private $_mode;
    
    private $_title;
    
    private $_text;
    
    /** Constructor
    */
    public function __construct($mode) {
	$this->_mode = $mode;
    }
    
    public function mode() {
	return $this->_mode;
    }
    
    public function title() {
	return $this->_title;
    }
    
    public function text() {
	return $this->_text;
    }
    
    public function setTitle($title) {
	$this->_title = $title;
    }
    
    public function setText($text) {
	$this->_text = $text;
    }
}

?>