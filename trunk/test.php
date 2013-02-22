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

// debug
error_reporting(E_ALL);

// Send image header to browser
if (!isset($_GET['debug'])) {
  header("Content-type: image/png"); 
}


$text = "start:A\r\nclass:'Ahoi' 'alias'\r\nclass: 'jaj'\r\nA->B:hallo";

// This should create a sample sequence diagram and display it

require_once('seqlib/sequence_parser.php');

$parser = new SequenceParser($text);
$parser->generateImage();

// $var = new SequenceDiagramBuilder();
// $var->setTitle('Example Sequence Diagram\nThis is just an example...');
// $result = true;
// $var->addNote('bla\nDid you see this thing ?\n did gggggggggggggggyou ???');
// $result &= $var->addMessage('A', 'B', 'request', false, true, 1);
// $var->addNote('bla\nDid you see this thing ?');
// 
// $result &= $var->addBlock('ALT', '[ client recognized ]');
// $result &= $var->addMessage('B', 'A', 'access allowed', true, true);
// $var->addNote('bla\nDid you see this thing ?');
// $result &= $var->splitBlock('[client unknown]');
// $result &= $var->addMessage('B', 'A', 'access denied', true, true);
// $result &= $var->endBlock();
// 
// 
// $result &= $var->addBlock('REF', 'other_diagram', true);
// $result &= $var->endBlock();
// 
// $result &= $var->addMessage('A', 'B', 'fu !!!', false, true,0,false,2);
// 
// 
// if (!$result) {
//     echo 'ERROR <br><br>';
// }
// 
// $var->draw();

?>