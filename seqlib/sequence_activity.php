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

require_once('sequence_object.php');

/** Represents an activity in the sequence diagram
*/
class SequenceActivity extends SequenceObject {    
    /** Offset for stacked activities
    */
    private $_xOffset;
    
    /** Reference to associated class
    */
    private $_class;
    
    /** Message starting activity
    */
    private $_message_start;
    
    /** Message ending activity
    */
    private $_message_end;
    
    /** Constructor
    */
    function __construct($class, $message) {
	$this->_class = $class;
	$this->_message_start = $message;
	$this->_message_end = NULL;
	$this->_xOffset = 0;
    }
    
    /** Draw this activity
    */
    function draw($draw) {
	$draw->drawActivity($this->_x + $this->_xOffset, $this->_y1, $this->_y2);
    }
    
    public function class_() {
	return $this->_class;
    }
    
    public function xOffset() {
	return $this->_xOffset;
    }
    
    public function setXOffset($xOffset) {
	$this->_xOffset = $xOffset;
    }
    
    public function setMessageEnd($message) {
	$this->_message_end = $message;
    }
    
    public function messageStart() {
	return $this->_message_start;
    }
    
    public function messageEnd() {
	return $this->_message_end;
    }
}

?>