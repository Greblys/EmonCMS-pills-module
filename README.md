EmonCMS-pills-module
====================

A module for EmonCMS which displays a form where you can set up a schedule for you pill reminder system.

##Requirements

* EmonCMS

##Installation

* Go to: path to EmonCMS installation/Modules
* Create new folder called "pills"
* Copy all the files from this repository into "pills" folder
* That's it! You can check it by going to http://yourdomain.com/pills/configure

##Possibe URL's for this module

* http://yourdomain.com/pills/configure - The main form for setting up schedule
* http://yourdomain.com/pills/configure.json - You can see here the current schedule set up in json format
* http://yourdomain.com/pills/pillNames.json - The list of all pill names entered into databse. *Used by module itself. Probably not very useful for a user.*

##Features

* Form includes autocomplete functionality which helps user to enter pill name. This is chosen instead of having huge list where the user would need to scroll a lot to find particular pill.
* Button for copying settings from one cell to all others. This way user doesn't need to fill in repetitive data for all cells 28 times.
* Button for copying settings from one day to all others.
