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

// This should create a sample sequence diagram and display it

//require_once('seqlib/sequence_draw.php');
require_once('seqlib/sequence_builder.php');

$var = new SequenceDiagramBuilder();

$result = true;

$result &= $var->addMessage('A', 'A', '', false, false, 1, true);
$result &= $var->addMessage('A', 'A', 'I like cheese\nYou think so ???????\nI like cheese\nI like cheese', false, true);
$result &= $var->addMessage('A', 'A', 'I like cheese', true, false);

$result &= $var->addMessage('A', 'B', '<<create>>', true, false, 0, false, 1);

  
$result &= $var->addMessage('A', 'B', 'request\nyaaay\nhaaay\nhaaay\nyaaay', true, false, 1);
$result &= $var->addMessage('B', 'A', 'reply', true, true, 2);
$result &= $var->addMessage('A', 'A', '', false, false, 2, true);
$result &= $var->addMessage('B', 'B', '<<destroy>>', true, false, 1, false, 2);


if (!$result) {
    echo 'ERROR <br><br>';
}

$var->draw();

?>