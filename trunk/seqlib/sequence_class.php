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

require_once('sequence_activity.php');
require_once('sequence_object.php');

/** Represents a class in the sequence diagram
*/
class SequenceClass extends SequenceObject {
    /** Array if references to incomming messages, i.e., messages whose destination is this class
    */
    private $_incomming_messages = array();
    
    /** Array if references to outgoing messages, i.e., messages whose origin is this class
    */
    private $_outgoing_messages = array();
    
    /** End y-coordinate of the lifeline
    */
    private $_lifeline_y;
    
    /** Array of activities, associated with this class
    */
    private $_activities = array();
    
    /** Indicates manual creation of this class
    * If set to true, the first incomming message indicates the creator and therefore will be handled specially
    */
    private $_manual_create;
    
    /** Indicates manual destruction of this class
    * If set to true, the last incomming message indicates the destructor and therefore will be handled specially
    */
    private $_manual_destroy;
    
    /** Constructor
    */
    function __construct($name, $alias) {
	$this->_name = $name;
	$this->_alias = $alias;
	
	$this->_lifeline_y = 0;
	$this->_manual_create = false;
	$this->_manual_destroy = false;
    }
    
    function manualCreate() {
	return $this->_manual_create;
    }
    
    function setManualCreate($manual) {
	$this->_manual_create = $manual;
    }
    
    function manualDestroy() {
	return $this->_manual_destroy;
    }
    
    function setManualDestroy($manual) {
	$this->_manual_destroy = $manual;
    }
    
    /** Set the x-coordinate of this class
    * If used, this function automatically adjusts the x-coordinate of all contained activities
    */
    public function setX($x) {
	$this->_x = $x;
	
	// Assign activity x-coordinate
	foreach($this->_activities as $activity) {
	    $activity->setX($x);
	}
    }

    function lifelineY() {
	return $this->_lifeline_y;
    }
    
    function setLifelineY($y) {
	$this->_lifeline_y = $y;
    }
    
    public function addIncommingMessage($message) {
	$this->_incomming_messages[] = $message;
    }
    
    public function addOutgoingMessage($message) {
	$this->_outgoing_messages[] = $message;
    }
    
    public function incommingMessages() {
	return $this->_incomming_messages;
    }
    
    public function outgoingMessages() {
	return $this->_outgoing_messages;
    }
    
    public function addActivity($activity) {
	$this->_activities[] = $activity;
    }
    
    /** Ends an activity
    * This method ends the current activity - which is the last activity which is not closed
    * @param message The end message, which closes this activity
    */
    public function endActivity($message) {
	$count = count($this->_activities);
	
	// Check for error
	if ($count == 0) {
	    return;
	}
	
	$last_activity = NULL;
	
	// Search last activity in array, which has no message indicating the end of the activity
	for ($i = $count - 1; $i >= 0; $i--) {
	    $activity = $this->_activities[$i];
	    
	    // If current activity has no ending message, which means, is not closed
	    if ($activity->messageEnd() == NULL) {
		$last_activity = $activity;
		break;
	    }
	}
	
	if ($last_activity != NULL) {
	    $last_activity->setMessageEnd($message);
	}
    }
    
    public function activities() {
	return $this->_activities;
    }
	    
    function draw($draw) {
	// Box with class name centered
	$draw->drawClassBox($this->_x, $this->_y, $this->_width, $this->_height, $this->_name);
	
	// Lifeline down the class
	$draw->drawClassLifeLine($this->_x, $this->_y + $this->_height / 2, $this->_lifeline_y);
	
	// Activities
	for ($i = 0; $i < count($this->_activities); $i++) {
	    $this->_activities[$i]->draw($draw);
	}	
    }
}

?>