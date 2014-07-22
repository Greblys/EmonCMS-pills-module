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

[More information](https://github.com/emoncms/development/blob/master/Modules/myelectric_tutorial/readme.md) about modules in emonCMS.

##Possibe URL's for this module

* http://yourdomain.com/pills/configure - The main form for setting up schedule
* http://yourdomain.com/pills/configure.json - You can see here the current schedule set up in json format
* http://yourdomain.com/pills/pillNames.json - The list of all pill names entered into databse. *Used by module itself. Probably not very useful for a user.*

##Database Schema
    CREATE TABLE Cells (
				   user_id INT,
				   deadline INT,
				   importance TINYINT,
				   cell_index TINYINT,
				   PRIMARY KEY (user_id, cell_index)
				  )
    
    CREATE TABLE Pill_names (
				   name VARCHAR(50) PRIMARY KEY NOT NULL
				  )
				  
    CREATE TABLE Names_in_cells (
					user_id INT NOT NULL,
					cell_index TINYINT NOT NULL,
					name VARCHAR(50) NOT NULL,
					PRIMARY KEY (user_id, cell_index, name),
					FOREIGN KEY (user_id, cell_index) REFERENCES Cells(user_id, cell_index),
					FOREIGN KEY (name) REFERENCES Pill_names(name)
					)
					
##Features

* Form includes autocomplete functionality which helps user to enter pill name. This is chosen instead of having huge list where the user would need to scroll a lot to find particular pill.
* Button for copying settings from one cell to all others. This way user doesn't need to fill in repetitive data for all cells 28 times.
* Button for copying settings from one day to all others.
