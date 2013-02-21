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
require_once('sequence_class.php');
require_once('sequence_diagram.php');
require_once('sequence_draw.php');
require_once('sequence_message.php');

/** Provides User Interface for building a sequence diagram and drawing
*/
class SequenceDiagramBuilder {
    private $_sequenceDiagram;
    private $_draw;
    private $_im;
    
    private $_classMinDistance = 50;
    private $_height = 0;
    private $_width = 0;
    
    /** Constructor
    */
    public function __construct() { 
	$this->_sequenceDiagram = new SequenceDiagram();
	$this->_draw = new Draw();
    }
    
    /** Add a class to the sequence diagram, className or optional aliasName should be unique
    * @param className The unique name of the class
    * @param aliasName The unique alias name of the class
    * @return True if names are unique and class was created, otherwise false if class or alias already exists
    */
    public function addClass($className, $aliasName = '') {
	// Set AliasName to ClassName if not given
	if ($aliasName == '') {
	    $aliasName = $className;
	}
	
	// Search if ClassName and AliasName are unique
	if ($this->_sequenceDiagram->existsClassByName($className) != NULL &&
	    $this->_sequenceDiagram->existsClassByName($aliasName) != NULL &&
	    $this->_sequenceDiagram->existsClassByAlias($aliasName) != NULL &&
	    $this->_sequenceDiagram->existsClassByAlias($className) != NULL) {
	    return false;
	}
	
	// Add new Class
	$class = new SequenceClass($className, $aliasName);
	
	// Compute Measures
	$width = 0;
	$height = 0;
	$this->_draw->classMeasures($className, $width, $height);
	$class->setMeasures($width, $height);
	
	$this->_sequenceDiagram->addClass($class);
	
	return true;
    }
    
    /** Start a new block
    * @param title The title of the block
    * @param text Inner text of the block
    */
    public function addBlock($title, $text) {
    
    }
    
    /** Splits the current block, e.g., for if/else conditions
    * @param text Inner text of the block
    */
    public function splitBlock($text) {
    
    }
    
    /** Closes current block
    */
    public function endBlock() {
    
    }
    
    /** Add a message to the sequence diagram
    * @param origin The origin component, identified by the class name or the alias name
    * @param destination The destination component, identified by the class name or the alias name
    * @param message_text The message text
    * @param dashed Optional argument to indicate a dashed line. Default is not dashed
    * @param fill_arrow_head Optional argument to indicate filled arrow head. Default is not filled
    * @param activity 0 = no activity, 1 = activity starts with incomming message, 2 = activity ends with outgoing message
    * @param invisible if true, message will be invisible (used for creating explicit activity and destroying explicit activity)
    * @param class_mode 0 = no mode, 1 = create the destination class explicit, 2 = destroy the destination class explicit
    */
    public function addMessage($origin, $destination, $message_text, $dashed = false, $fill_arrow_head = false, $activity_mode = 0, $invisible = false, $class_mode = 0) {
	// Add classes origin and destination if new
	$origin_create = $this->addClass($origin);
	$destination_create = $this->addClass($destination);
	
	if ($class_mode == 1 && $destination_create == false) {
	    // Trying to explicit create an existing class => error
	    return false;
	}
	
	if ($class_mode == 2 && $destination_create == true) {
	    // Trying to explicit destroy a non existing class => error
	    return false;
	}
	
	// Get origin class
	$origin_class = $this->_sequenceDiagram->findClassByNameOrAlias($origin);
	$destination_class = $this->_sequenceDiagram->findClassByNameOrAlias($destination);
	
	// Check if trying to send a message to a destroyed class
	if ($destination_class->manualDestroy()) {
	    return false;
	}
	
	// Add message
	$message = new SequenceMessage($origin, $destination, $message_text, $dashed, $fill_arrow_head);
	$message->setVisible(!$invisible);
	
	$this->_sequenceDiagram->addMessage($message);
	
	// Start activity at destination
	if ($activity_mode == 1) {
	    $activity = new SequenceActivity($destination_class, $message);
	    $destination_class->addActivity($activity);
	    
	    $message->setStartingActivity($activity);
	}
	
	// End activity at origin
	if ($activity_mode == 2) {	    
	    $class_activities = $origin_class->activities();
	    $count = count($class_activities);
	    $last_activity = NULL; //$class_activities[$count - 1];
	    
	    // Search backwards last not closed activity
	    for ($i = $count - 1; $i >= 0; $i--) {
		$activity = $class_activities[$i];
		if ($activity->messageEnd() == NULL) {
		    $last_activity = $activity;
		    break;
		}
	    }
	    
	    if ($last_activity != NULL) {
		$message->setEndingActivity($last_activity);
		$origin_class->endActivity($message);
	    }
	}
	
	// Create or destroy class
	if ($class_mode != 0) {	    
	    switch ($class_mode) {
		case 1:
		    $destination_class->setManualCreate(true);
		    break;
		case 2:
		    $destination_class->setManualDestroy(true);
		    break;
	    }
	}
	
	return true;
    }
        
    private function computePositions() {	    
	$x = 25;
	$y = 50;
	
	$this->_width = $x;
		
	// 1. Compute x-coordinates for all classes
	foreach ($this->_sequenceDiagram->classes() as $id => $class) {
	    $x += $class->width() / 2;
    
	    $class->setX($x);
	    $class->setY($y);
	    $x += $class->width() / 2 + $this->_classMinDistance;	
	}
	
	$y = 90;
	$message_space = 50;
	$width_increment = 30;
	
	$this->_height = $y;
	
	$last_message_was_invisible = false;
	
	// 2. Compute y-coordinates for all messages
	foreach ($this->_sequenceDiagram->messages() as $id => $message) {
	    // Check if not self message
	    $self_message = ($message->origin() == $message->destination());
	    
	    // If invisible message, subtract a little bit of heigh
	    if (!$message->visible() && !$last_message_was_invisible) {
		  // Adjust height
		  $y -= 20;
		  $this->_height -= 20;
	    }
	  	    
	    // Add text height before anything
	    $y += $this->_draw->messageHeight($message->text(), $self_message);
	    $this->_height += $this->_draw->messageHeight($message->text(), $self_message);
	    
	    // Reset
	    $last_message_was_invisible = false;
	    
	    // Set current y-position for message
	    $message->setY($y);
	    
	    // If message exceeds current distance between classes, extend it
	    $extend = false;
	    
	    $new_distance = 0;
	    $current_distance = 0;
	    $direction = false;
	    
	    if (!$self_message) {		    
		// 3. Compute new distance between classes
		$x_origin = $this->_sequenceDiagram->findClassByNameOrAlias($message->origin())->x();
		$x_destination = $this->_sequenceDiagram->findClassByNameOrAlias($message->destination())->x();
		
		$current_distance = abs($x_destination - $x_origin);
		$direction = $x_destination > $x_origin;
		
		$new_distance = $this->_draw->messageWidth($message->text());
	    }
	    else {
		// Get right neighbour
		$origin = $this->_sequenceDiagram->findClassByNameOrAlias($message->origin());
		$right_neighbour = NULL;
		$origin_found = false;
		
		foreach ($this->_sequenceDiagram->classes() as $class) {
		    if ($origin_found) {
			$right_neighbour = $class;
			break;
		    }
		    
		    if ($class == $origin) {
			$origin_found = true;
		    }
		}
				
		// Compute distance to neighbour
		if ($right_neighbour != NULL) {
		    $x_origin = $origin->x();
		    $x_destination = $right_neighbour->x();
		    
		    $current_distance = abs($x_destination - $x_origin);
		    $direction = $x_destination > $x_origin;
		    
		    $new_distance = $this->_draw->messageWidth($message->text(), true);
		}
	    }
	    
	    if ($new_distance > $current_distance) {
		    $extend = true;
	    }
	    
	    $difference = $new_distance - $current_distance;
		
	    $found = false;
	    $skip = false;
	    
	    // Iterate through all classes right of destination and add difference
	    foreach ($this->_sequenceDiagram->classes() as $id => $class) {
		// While iterating through all classes here, we can use it to register message to class
		if ($class->name() == $message->origin() ||
		    $class->alias() == $message->origin()) {
			$class->addOutgoingMessage($message);
		    }
		
		if ($class->name() == $message->destination() ||
		    $class->alias() == $message->destination()) {
			$class->addIncommingMessage($message);
		    }
		    
		$first_message_in_class = NULL;
		if (count($class->incommingMessages()) > 0) { $first_message_in_class = $class->incommingMessages()[0]; }
		
		$last_message_in_class = NULL;
		if (count($class->incommingMessages()) > 0) { $last_message_in_class = $class->incommingMessages()[count($class->incommingMessages()) - 1]; }
		
		// If manual class create and this is creating message
		if ($class->manualCreate() && $message == $first_message_in_class) {
		    // Move class box horizontally
		    $current_x = $class->x();
		    $current_x += $class->width() / 2;
		    $class->setX($current_x);
		    
		    // Move class box vertically
		    $class->setY($message->y());
		}
		
		// If manual class destroy and this is destroying message
		if ($class->manualDestroy() && $message == $last_message_in_class) {
		    if ($self_message) {
			$class->setLifelineY($message->y() + 50);
		    }
		    else {
			$class->setLifelineY($message->y() + 30);
		    }
		}
			
		// Search class
		if ($extend) {			    
		    if (!$found &&
			$direction == true &&
			($class->name() == $message->destination() ||
			  $class->alias() == $message->destination())) {
			    $found  = true;
		    }
			    
		    if (!$found &&
			$direction == false &&
			($class->name() == $message->origin() ||
			  $class->alias() == $message->origin())) {
			    $found  = true;
		    }
		    
		    
		    // If selfmessage, skip one class to get right neighbour instead
		    if ($found && $message->visible() && $self_message && !$skip) { 		    
			$skip = true; continue; 
		    }
			    
		    if ($found && $id != 0 && $message->visible()) {
			$current_x = $class->x();
			$current_x += $difference;
			
			// If activitties in class, add width/2
			if (count($class->activities()) > 0) {
			    $current_x += 5;
			}
			
			$class->setX($current_x);
		    }
		}
	    }
	    
	    // If invisible message, only increment a little bit
	    if (!$message->visible()) {
		  // Increment height
		  $y += 0 ;
		  $this->_height += 0;
		  $last_message_was_invisible = true;
	    }
	    else {
		  // Increment height
		  $y += 20 ;
		  $this->_height += 20;
	      
		  // Self message has different heigh then common message
		  if ($self_message) {
		      $y += $this->_draw->messageHeight($message->text(), true);
		      $this->_height += $this->_draw->messageHeight($message->text(), true);
		  }
	    }
	}
	
	$this->_height += 25;
	
	// Compute x-coordinates for messages
	foreach ($this->_sequenceDiagram->classes() as $id => $class) {
	    $outgoingMessages = $class->outgoingMessages();
	    $incommingMessages = $class->incommingMessages();
				    
	    for ($i=0;$i<count($outgoingMessages);$i++) {
		$outgoingMessages[$i]->setX1($class->x());
	    }
	    
	    for ($i=0;$i<count($incommingMessages);$i++) {
		
		$incommingMessages[$i]->setX2($class->x());
	    }
	    
	    // For messages creating a class, slightly shift message horizontally
	    if ($class->manualCreate()) {
		$x1 = $incommingMessages[0]->x1();
		$x2 = $incommingMessages[0]->x2();
		
		if ($x1 > $x2) {
		    $incommingMessages[0]->setX2($class->x() + $class->width() / 2 + 1);
		}
		else {
		    $incommingMessages[0]->setX2($class->x() - $class->width() / 2 - 1);
		}		
	    }
	}
	
	// Compute activities
	foreach ($this->_sequenceDiagram->classes() as $id => $class) {
	    $activities = $class->activities();
	    
	    // Iterate over activities for this class
	    for ($i = 0; $i < count($activities); $i++) {
		$activity = $activities[$i];
		
		$start_found = false;
		$end_found = false;
		
		// Iterate over all messages and find start and end, move all messages in beetween
		foreach ($this->_sequenceDiagram->messages() as $id_message => $message) {
		    // Check if not self message
		    $self_message = ($message->origin() == $message->destination());
		
		    // Search start
		    if ($message == $activity->messageStart()) {
			$start_found = true;
			
			// Assign coordinate to activity
			if (!$self_message || !$message->visible()) {
			    $activity->setY1($message->y());
			    $activity->setY2($message->y());
			}
			else {
			    $activity->setY1($message->y() + $this->_draw->messageHeight($message->text(), true));
			    $activity->setY2($message->y() + $this->_draw->messageHeight($message->text(), true));
			}
		    }
		    
		    // Move messages in between
		    
		    // Going out
		    if ($start_found && ($message->origin() == $class->name() || $message->origin() == $class->alias())) {
			
			// Selfmessage should be ignored
			if (! ($self_message && $message == $activity->messageStart())) {
			    $x1 = $message->x1();
			    $x2 = $message->x2();
			    
			    // Move message
			    if ($x2 >= $x1) {
				$message->setX1($x1 + 6);
			    }
			    else {
				$message->setX1($x1 - 6);
			    }
			}
			// Increment activity height in case we have no explicit end found
			$activity->setY2($activity->y2() + 40);
		    }
		    
		    // Going in
		    if ($start_found && ($message->destination() == $class->name() || $message->destination() == $class->alias())) {
			// Test for stacked activities
			if ($self_message && $message->startingActivity() != NULL && $message->startingActivity() != $activity) {
			    $xOffset = $message->startingActivity()->xOffset();
			    $message->startingActivity()->setXOffset($xOffset + 6);
			}
		    
			$x1 = $message->x1();
			$x2 = $message->x2();
			
			// Move message
			if ($x2 > $x1) {
			    $message->setX2($x2 - 6);
			}
			else {
			    $message->setX2($x2 + 6);
			}
			
			// Increment activity height in case we have no explicit end found
			$activity->setY2($activity->y2() + 40);
		    }
		    
		    // Search end message
		    if ($message == $activity->messageEnd()) {			
			// Assign coordinate to activity
			$activity->setY2($message->y());
			
			// Mark end found, else we need to end activity manual
			$end_found = true;
			
			// End iteration here
			break;
		    }
		}
		
		// If no explicit end found, end activity manual
		if (!$end_found) {
		    $activity->setY2($activity->y2() - 40);
		}
		
		// Check if activity does not overlap with end of lifeline
		if ($class->lifelineY() != 0 && $activity->y2() > $class->lifelineY()) {
		    $class->setLifelineY($activity->y2() + 30);
		}
	    }
	}
	
	// Adjust image height
	$this->_height += 50;
	
	// Get image width
	
	// Most right class
	$classes = $this->_sequenceDiagram->classes();
	$max = count($classes);
	$max_class = $classes[$max - 1];
	
	$this->_width = $max_class->x() + $max_class->width() / 2 + 25;
	
	// Most right self message
	$messages = $max_class->outgoingMessages();
	for ($i = 0; $i < count($messages); $i++) {
	    $message = $messages[$i];
	    
	    // Check if this message is a self message
	    $self_message = ($message->origin() == $message->destination());
	    
	    if ($self_message) {
		// Compute width of the self message
		$distance = $this->_draw->messageWidth($message->text(), true);
		
		// Compute maximal right position of the self message in the image
		$x_right = $max_class->x() + $distance;
		
		// If the new position exceeds the image, extend the image width
		if ($x_right > $this->_width) {
		    $this->_width = $x_right;
		}
	    }
	}
	
	// Most right note..etc: TODO: do not forget to adjust image width for new elements
    }
    
    public function draw() {
	// Compute all positions
	$this->computePositions();
	
	// Draw sequence diagram
	$this->_draw->createImage($this->_width, $this->_height);

	// Draw classes and lifelines
	foreach ($this->_sequenceDiagram->classes() as $id => $class) {
	    $class->draw($this->_draw);
	}
			
	// Draw messages
	foreach ($this->_sequenceDiagram->messages() as $id => $message) {
	    $message->draw($this->_draw);
	}
	
	$this->_draw->display();
    }
}

?>