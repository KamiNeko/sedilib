# Introduction #

SeDiLib (Sequence Diagram Library) is a small project to create simple sequence diagrams online with a scripting language.


It is split into following parts:
  * [Layer](Parser.md)
    * Parses scripts and redirects commands to builder
  * [Layer](Builder.md)
    * Builds sequence diagram with commands
  * [Diagram Structure Layer](Sequence.md)
    * Structure of a sequence diagram: classes, messages, activities, etc.
  * [Layer](Drawing.md)
    * Draw a sequence diagram, preferable with different skins

# Features #
  * Set explicit order of classes
  * Give class short alias name
  * Send messages between classes
  * Different message types: Filled / empty arrow head, dashed line
  * Start and stop activities with incomming / outgoing messages
  * Explicit start and stop activity in a class
  * Create or destroy a class with a message
  * Use blocks (OPT, ALT, REF), you can use own titles
  * Set title of diagram
  * Add notes to diagram in the flow

# Scripting Language #
Every line is parsed as a command. Whitespaces are automatically trimmed, lower and uppercase are ignored. Commands are listed in sequence\_parser.php

# TODO #
  * Skins Layer
  * Overall Cleanup
  * Plugins
    * DokuWiki Plugin