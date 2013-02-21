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

/** 
* An abstract class representing the interface for arbitrary sequence diagram objects
* Provides most useful attributes like position, measures, title, text
*/
abstract class SequenceObject {
    /** Bounding box of this object
    */
    protected $_x1;
    protected $_y1;
    protected $_x2;
    protected $_y2;
    
    /** Center position
    */
    protected $_x;
    protected $_y;
    
    /** Measures
    */
    protected $_width;
    protected $_height;
    
    /** If set to false, this object should not be visible and should not affect positions of subsequent objects in the diagram
    */
    protected $_visible;
    
    /** Name of this object, can also be used to display a title of the object
    */
    protected $_name;
    
    /** Alias name of this object, can be used to explicit use a shorter name for this object but display the full
    * name instead
    */
    protected $_alias;
    
    /** Text of this object
    */
    protected $_text;
    
    public function name() {
	return $this->_name;
    }
    
    public function alias() {
	return $this->_alias;
    }
    
    public function text() {
	return $this->_text;
    }
    
    public function setName($name) {
	$this->_name = $name;
    }
    
    public function setAlias($alias) {
	$this->_alias = $alias;
    }
    
    public function setText($text) {
	$this->_text = $text;
    }
    
    public function x1() {
	return $this->_x1;
    }
    
    public function x2() {
	return $this->_x2;
    }
    
    public function y1() {
	return $this->_y1;
    }
    
    public function y2() {
	return $this->_y2;
    }
    
    public function setX1($x1) {
	$this->_x1 = $x1;
    }
    
    public function setX2($x2) {
	$this->_x2 = $x2;
    }
    
    public function setY1($y1) {
	$this->_y1 = $y1;
    }
    
    public function setY2($y2) {
	$this->_y2 = $y2;
    }
    
    public function setPosition($x, $y) {
	$this->_x = $x;
	$this->_y = $y;
    }
    
    public function visible() {
	return $this->_visible;
    }
    
    public function setVisible($visible) {
	$this->_visible = $visible;
    }
    
    public function x() {
	return $this->_x;
    }
    
    public function setX($x) {
	$this->_x = $x;
    }
    
    public function y() {
	return $this->_y;
    }
    
    public function setY($y) {
	$this->_y = $y;
    }
    
    public function width() {
	return $this->_width;
    }
    
    public function setWidth($width) {
	$this->_width = $width;
    }
    
    public function height() {
	return $this->_height;
    }
    
    public function setHeight($height) {
	$this->_height = $height;
    }
    
    public function setMeasures($width, $height) {
	$this->_width = $width;
	$this->_height = $height;
    }
}

?>