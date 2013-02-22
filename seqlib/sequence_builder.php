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
require_once('sequence_block.php');
require_once('sequence_block_action.php');
require_once('sequence_class.php');
require_once('sequence_diagram.php');
require_once('sequence_draw.php');
require_once('sequence_message.php');
require_once('sequence_note.php');

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
	if ($this->_sequenceDiagram->findClassByNameOrAlias($className) != NULL &&
	    $this->_sequenceDiagram->findClassByNameOrAlias($aliasName) != NULL) {
	    return false;
	}
	
	// Add new Class
	$class = new SequenceClass($className, $aliasName);
	
	// Compute Measures
	$width = 0;
	$height = 0;
	$this->_draw->classMeasures($className, $width, $height);
	$class->setMeasures($width, $height);
	
	$this->_sequenceDiagram->addObject($class);
	
	return true;
    }
    
    public function setTitle($title) {
	$this->_sequenceDiagram->setTitle($title);
    }
    
    public function addNote($text, $invisible = false) {
	// Add note
	$note = new SequenceNote($text);
	$note->setVisible(!$invisible);
	
	$this->_sequenceDiagram->addObject($note);
    }
    
    /** Start a new block
    * @param title The title of the block
    * @param text Inner text of the block
    */
    public function addBlock($title, $text, $empty = false) {
	if ($empty) {
	    $action = new SequenceBlockAction('create_empty');
	}
	else {
	    $action = new SequenceBlockAction('create');
	}
	
	$action->setTitle($title);
	$action->setText($text);
	
	$this->_sequenceDiagram->addObject($action);
	return true;
    }
    
    /** Splits the current block, e.g., for if/else conditions
    * @param text Inner text of the block
    */
    public function splitBlock($text) {
	$action = new SequenceBlockAction('split');
	$action->setText($text);
	
	$this->_sequenceDiagram->addObject($action);
	
	return true;
    }
    
    /** Closes current block
    */
    public function endBlock() {
	$action = new SequenceBlockAction('close');
	
	$this->_sequenceDiagram->addObject($action);
	
	return true;
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
    public function addMessage($origin, $destination, $message_text, $dashed = false, $fill_arrow_head = true, $activity_mode = 0, $invisible = false, $class_mode = 0) {
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
	
	$this->_sequenceDiagram->addObject($message);
	
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
	    $last_activity = NULL; 
	    
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
    
    /** Returns true if object is a class
    */
    private function isClass($object) {
	return get_class($object) == 'SequenceClass';
    }
    
    /** Returns true if object is a message
    */
    private function isMessage($object) {
	return get_class($object) == 'SequenceMessage';
    }
    
    private function isNote($object) {
	return get_class($object) == 'SequenceNote';
    }
    
    /** Returns true if object is a block action
    */
    private function isBlockAction($object) {
	return get_class($object) == 'SequenceBlockAction';
    }
    
    private function computeClassesInitialX(&$x, &$y) {
	foreach ($this->_sequenceDiagram->objects() as $class) {
	    // Only search for classes
	    if (!$this->isClass($class)) {
		continue;
	    }
	    
	    $x += $class->width() / 2;
	    $class->setX($x);
	    $class->setY($y);
	    $x += $class->width() / 2 + $this->_classMinDistance;	
	}
    }
    
    private function computeBlocks() {
	$max_width = -1;
	
	foreach ($this->_sequenceDiagram->blocks() as $block) {
	    $block->setWidth($this->_width, $this->_draw);
	    if ($block->width() > $max_width) {
		$max_width = $block->width();
	    }
	}
	
	$this->resizeWidth($max_width + 20);
	
	foreach ($this->_sequenceDiagram->blocks() as $block) {
	    $block->adjustX($this->_width / 2);
	}
    }
    
    private function computeBlockAction($blockAction, &$x, &$y, &$current_block, &$current_sub_block) {
	switch ($blockAction->mode()) {
	    case 'create_empty':
	    case 'create':
		$y += 10;
		$this->_height += 10;
		
		// Create new block and sub block
		$block = new SequenceBlock();
		$subBlock = new SequenceSubBlock();
		
		if ($blockAction->mode() == 'create_empty') {
		    $block->setEmptyfill(true);
		}
		
		// Assign tree relation
		$block->setParentPtr($current_block);
		$block->addInnerSubBlock($subBlock);
		
		$subBlock->setParentPtr($block);
		
		if ($current_sub_block != NULL) {
		    $current_sub_block->addInnerBlock($block);
		}
		else {
		    // We are on first layer of hierarchy so add directly to sequence diagram
		    $this->_sequenceDiagram->addBlock($block);
		}
		
		// Reassign current block and sub block
		$current_block = $block;
		$current_sub_block = $subBlock;
		
		// Set attributes
		$block->setName($blockAction->title());
		$subBlock->setText($blockAction->text());
		
		$subBlock->setY1($y);
		
		// Add text height
		$text_height = $this->_draw->minBlockHeight($current_block->name(), $current_sub_block->text());
		$y += $text_height;
		$this->_height += $text_height;
		
		break;
		
	    case 'split':
		$y += 10;
		$this->_height += 10;
	
		// Close current sub block
		$current_sub_block->setY2($y);
		
		// Create new sub block
		$subBlock = new SequenceSubBlock();
		
		// Assign to current block
		$current_block->addInnerSubBlock($subBlock);
		$subBlock->setParentPtr($current_block);
		
		// Reassign
		$current_sub_block = $subBlock;
		
		// Set attributes
		$subBlock->setText($blockAction->text());
		
		$subBlock->setY1($y);
		
		// Add text height
		$text_height = $this->_draw->minBlockHeight($current_block->name(), $current_sub_block->text());
		$y += $text_height;
		$this->_height += $text_height;
		
		break;
		
	    case 'close':
		$y += 10;
		$this->_height += 10;
		// Close current sub block
		$current_sub_block->setY2($y);
		
		// Search last created sub block in parent block
		if ($current_block != NULL && $current_block->parentPtr() != NULL) {
		    $sub_blocks = $current_block->parentPtr()->innerSubBlocks();
		    $max_count = count($sub_blocks);
		    $last_created_sub_block = $sub_blocks[$max_count - 1];
		    
		    // Reassign
		    $current_block = $last_created_sub_block->parentPtr();
		    $current_sub_block = $last_created_sub_block;
		}
		// Else we are on top of the hierarchy
		else {
		    $current_block = NULL;
		    $current_sub_block = NULL;
		}
		
		break;
	}
	
	// Increment y
	$y += 20;
	$this->_height += 20;
    }
    
    private function computeNoteY($note, &$x, &$y) {
	$height = $this->_draw->noteHeight($note->text());
	
	$note->setY($y);
	
	$y += $height + 10;
	
	$this->_height += $height + 10;
    }
    
    private function computeNoteX() {
	$x = $this->_width + 20;
	
	$max = -1;
	
	foreach ($this->_sequenceDiagram->objects() as $note) {
	    // Note
	    if (!$this->isNote($note)) {
		continue;
	    }
	    
	    $note->setX($x);
	    
	    $current_x2 = $x + $this->_draw->noteWidth($note->text());
	    
	    if ($current_x2 > $max) {
		$max = $current_x2;
	    }
	}
	
	if ($max + 10 > $this->_width) {
	    $this->_width = $max + 10;
	}
    }
    
    private function computeMessagesY(&$x, &$y) {
	$last_message_was_invisible = false;
	
	$current_block = NULL;
	$current_sub_block = NULL;
	
	foreach ($this->_sequenceDiagram->objects() as $message) {
	    // Block action
	    if ($this->isBlockAction($message)) {
		$this->computeBlockAction($message, $x, $y, $current_block, $current_sub_block);
		continue;
	    }
	    
	    // Note
	    if ($this->isNote($message)) {
		$this->computeNoteY($message, $x, $y);
		continue;
	    }
	    
	    // Only search for messages
	    if (!$this->isMessage($message)) {
		continue;
	    }
	    
	    // Check if not self message
	    $self_message = ($message->origin() == $message->destination());
	    
	    // If invisible message, subtract a little bit of height
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
		
		foreach ($this->_sequenceDiagram->objects() as $class) {
		    // Only search for classes
		    if (!$this->isClass($class)) {
			continue;
		    }
		    
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
	    foreach ($this->_sequenceDiagram->objects() as $id => $class) {
		// Only search for classes
		if (!$this->isClass($class)) {
		    continue;
		}
	    
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
			$skip = true; 
			continue; 
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
	      
		  // Self message has different height then common message
		  if ($self_message) {
		      $y += $this->_draw->messageHeight($message->text(), true);
		      $this->_height += $this->_draw->messageHeight($message->text(), true);
		  }
	    }
	}
    }
        
    private function computeMessagesX(&$x, &$y) {    
	foreach ($this->_sequenceDiagram->objects() as $id => $class) {
	    // Only search for classes
	    if (!$this->isClass($class)) {
		continue;
	    }
	    
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
    }
        
    private function computeActivites(&$x, &$y) {
	foreach ($this->_sequenceDiagram->objects() as $id => $class) {
	    // Only search for classes
	    if (!$this->isClass($class)) {
		continue;
	    }
	    
	    $activities = $class->activities();
	    
	    $last_y = -1;
	    	    
	    // Iterate over activities for this class
	    //for ($i = 0; $i < count($activities); $i++) {
	    for ($i = count($activities) - 1; $i >= 0; $i--) {
		$activity = $activities[$i];
				
		$start_found = false;
		$end_found = false;
		
		// Iterate over all messages and find start and end, move all messages in beetween
		foreach ($this->_sequenceDiagram->objects() as $message) {
		    // Only search for messages
		    if (!$this->isMessage($message)) {
			continue;
		    }
		    
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
			//$activity->setY2($activity->y2() + 40);
			$activity->setY2($message->y() + 20);
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
			//$activity->setY2($activity->y2() + 40);
			$activity->setY2($message->y() + 20);
			
			if ($self_message) {
			    $activity->setY2($message->y() + 40);
			}
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
		  //$activity->setY2($activity->y2() + 40);
		}
		
		// Check if activity does not overlap with end of lifeline
		if ($class->lifelineY() != 0 && $activity->y2() > $class->lifelineY()) {
		    $class->setLifelineY($activity->y2() + 30);
		}
		
		if ($activity->y2() == $last_y) {
		    $activity->setY2($activity->y2() + 10);
		}
		
		$last_y = $activity->y2();
	    }
	}
    }
        
    private function computeImageWidth() {
	if (count($this->_sequenceDiagram->objects()) == 0) {
	    return;
	}
	
	// Find most right class
	$index = -1;
	for ($i = count($this->_sequenceDiagram->objects()) - 1; $i >= 0; $i--) {
	    // Only search for classes	    
	    if (!$this->isClass($this->_sequenceDiagram->objects()[$i])) {
		continue;
	    }
	    
	    $index = $i;
	    break;
	}
	
	// Most right class
	$max_class = $this->_sequenceDiagram->objects()[$index];
	
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
	
	// Title width
	if ($this->_sequenceDiagram->title() != '') {
	    $title_width = $this->_draw->diagramTitleWidth($this->_sequenceDiagram->title());
	    
	    if ($this->_width < $title_width + 10) {
		$this->resizeWidth($title_width + 10);
	    }
	}
	
	// Most right note..etc: TODO: do not forget to adjust image width for new elements
    }
        
    private function computePositions() {	    
	$x = 25;
	$y = 50;
	
	if ($this->_sequenceDiagram->title() != '') {
	    $y += $this->_draw->diagramTitleHeight($this->_sequenceDiagram->title());
	}
	
	$this->_width = $x;
		
	// Compute initial x-coordinates for all classes by order of insertion
	$this->computeClassesInitialX($x, $y);
	
	$y += 30;
	
	$this->_height = $y;
	
	// Compute y-coordinates for all messages
	$this->computeMessagesY($x, $y);
	
	// Adjust height
	$this->_height += 25;
	
	// Compute x-coordinates for messages
	$this->computeMessagesX($x, $y);
	
	// Compute activities
	$this->computeActivites($x, $y);
	
	// Adjust image height approximately for destruction markers
	$this->_height += 30;
	
	// Set image width
	$this->computeImageWidth();
		
	// Compute blocks
	$this->computeBlocks();
	
	// Compute notes
	$this->computeNoteX();
    }
        
    private function resizeWidth($new_width) {
	$difference = $new_width - $this->_width;
	
	if ($difference <= 0) {
	    return;
	}
	
	foreach ($this->_sequenceDiagram->objects() as $object) {
	    if ($this->isClass($object) || $this->isMessage($object)) {
		$x1 = $object->x1();
		$x2 = $object->x2();
		$x = $object->x();
		
		$object->setX1($x1 + $difference / 2);
		$object->setX2($x2 + $difference / 2);
		$object->setX($x + $difference / 2);
	    }
	}
	
	$this->_width = $new_width;
    }
    
    public function draw($filename = '') {
	// Compute all positions
	$this->computePositions();
	
	// Draw sequence diagram
	$this->_draw->createImage($this->_width, $this->_height);
	
	// Draw contained objects
	foreach ($this->_sequenceDiagram->objects() as $object) {
	    if ($this->isClass($object) || $this->isMessage($object) || $this->isNote($object)) {
		$object->draw($this->_draw);
	    }
	}
	
	// Draw blocks
	foreach ($this->_sequenceDiagram->blocks() as $block) {
	    $block->draw($this->_draw);
	}
	
	// Draw title
	if ($this->_sequenceDiagram->title() != '') {
	    $this->_draw->drawDiagramTitle($this->_sequenceDiagram->title());
	}
	
	$this->_draw->display($filename );
    }
}

?>