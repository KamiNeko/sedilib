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

require_once('sequence_class.php');
require_once('sequence_message.php');

/** Represents the sequence diagramm
*/
class SequenceDiagram {
    private $_classes = array();
    private $_messages = array();
    
    function __construct() {
    
    }
    
    function addClass($class) {
	    $this->_classes[] = $class;
    }
    
    function addMessage($message) {
	    $this->_messages[] = $message;
    }
    
    function findClassByNameOrAlias($name) {
	for ($i=0; $i < count($this->_classes); $i++) {
	    if ($this->_classes[$i]->name() == $name) {
		return $this->_classes[$i];
	    }
	    
	    if ($this->_classes[$i]->alias() == $name) {
		return $this->_classes[$i];
	    }
	}
	
	return NULL;
    }
    
    function existsClassByName($className) {
	for ($i=0; $i < count($this->_classes); $i++) {
	    if ($this->_classes[$i]->name() == $className) {
		return true;
	    }
	}
	
	return false;
    }
    
    function existsClassByAlias($aliasName) {
	for ($i=0; $i < count($this->_classes); $i++) {
	    if ($this->_classes[$i]->alias() == $aliasName) {
		return true;
	    }
	}
	
	return false;
    }
    
    function classes() {
	return $this->_classes;
    }
    
    function messages() {
	return $this->_messages;
    }
}

?>