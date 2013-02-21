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

/** Represents a message in the sequence diagramm
*/
class SequenceMessage extends SequenceObject {
    private $_dashed;
    private $_arrowHead;
    
    private $_origin;
    private $_destination;
 
    private $_activity_starting = NULL;
    private $_activity_ending = NULL;
    
    function __construct($origin, $destination, $messageText, $dashed, $arrowHead) {
	$this->_origin = $origin;
	$this->_destination = $destination;
	$this->_text = $messageText;
	$this->_dashed = $dashed;
	$this->_arrowHead = $arrowHead;
    }
    
    function setStartingActivity($activity) {
	$this->_activity_starting = $activity;
    }
    
    function setEndingActivity($activity) {
	$this->_activity_ending = $activity;
    }
    
    function startingActivity() {
	return $this->_activity_starting;
    }
    
    function endingActivity() {
	return $this->_activity_ending;
    }
    
    function origin() {
	return $this->_origin;
    }
    
    function destination() {
	return $this->_destination;
    }
    
    function setOrigin($origin) {
	$this->_origin = $origin;
    }
    
    function setDestination($destination) {
	$this->_destination = $destination;
    }
            
    function dashed() {
	return $this->_dashed;
    }
    
    function arrowHead() {
	return $this->_arrowHead;
    }
    
    function draw($draw) {
	if (!$this->_visible) {
	    return;
	}
	
	$self_message = ($this->_origin == $this->_destination);
	$draw->drawMessage($this->_text, $this->_x1, $this->_x2, $this->_y, $this->_dashed, $this->_arrowHead, $self_message);
    }
}

?>