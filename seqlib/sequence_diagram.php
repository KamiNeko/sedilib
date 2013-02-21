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

require_once('sequence_block.php');
require_once('sequence_class.php');
require_once('sequence_message.php');

/** Represents the sequence diagramm
*/
class SequenceDiagram {
    /** Objects in the diagram
    */
    private $_objects = array();
    
    /** Blocks in the diagram
    */
    private $_blocks = array();
    
    /** Title of the diagram
    */
    private $_title;
    
    /** Constructor
    */
    public function __construct() {
	$_title = '';
    }
    
    /** Add an object to the diagram
    */
    public function addObject($object) {
	$this->_objects[] = $object;
    }
    
    /** Searches all objects in this diagram for classes with the given name or alias
    */
    public function findClassByNameOrAlias($name) {
	for ($i=0; $i < count($this->_objects); $i++) {
	    // Only search for classes
	    if (get_class($this->_objects[$i]) != 'SequenceClass') {
		continue;
	    }
	    
	    if ($this->_objects[$i]->name() == $name) {
		return $this->_objects[$i];
	    }
	    
	    if ($this->_objects[$i]->alias() == $name) {
		return $this->_objects[$i];
	    }
	}
	
	return NULL;
    }
    
    /** Returns all objects in this diagram
    */
    public function objects() {
	return $this->_objects;
    }
    
    public function addBlock($block) {
	$this->_blocks[] = $block;
    }
    
    public function blocks() {
	return $this->_blocks;
    }
    
    public function title() {
	return $this->_title;
    }
    
    public function setTitle($title) {
	$this->_title = $title;
    }
}

?>