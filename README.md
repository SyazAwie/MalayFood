Malay Traditional Food Heritage System - Setup Guide
This guide provides step-by-step instructions for setting up the Malay Traditional Food Heritage System.
________________________________________
1️) Clone the Project from GitHub
  1.	Open Command Prompt (CMD), Git Bash, or Terminal.
  2.	Navigate to the folder where to store the project: 
  cd C:\xampp\htdocs
  3.	Clone the code: 
  git clone https://github.com/SyazAwie/MalayFood.git
  4.	Navigate into the project folder: 
  cd MalayFood
________________________________________
2️) Download & Import the Database
  1.	Download the Database:
    o	Open Google Drive link (https://drive.google.com/drive/folders/11ioCmTOrFbqcbv3BVqqh06-yYSETC9OA?usp=drive_link ).
    o	Download:  malay_traditional_food_heritage_system.sql.
    o	OR download malay_traditional_food_heritage_system.sql file at this Repositories.
  2.	Import the Database into XAMPP:
    o	Open XAMPP Control Panel and start MySQL & Apache.
    o	Open a browser and go to: 
      http://localhost/phpmyadmin
    o	Click "New", enter the database name: 
      malay_traditional_food_heritage_system
    o	Click Create.
    o	Click Import > Choose File > Select malay_traditional_food_heritage_system.sql.
    o	Click Import to import the database.
________________________________________
3) Run the Website
  1.	Open XAMPP Control Panel, make sure Apache & MySQL are running.
  2.	Open a browser and visit: 
      http://localhost/MalayFood/home.php
________________________________________
4) Username, password and role for users already in the database:
  Username	  Password	  Role
  Harris	    harris73	  User
  Admin123	  Admin12345	Admin
  moderator	  12345	      Moderator

