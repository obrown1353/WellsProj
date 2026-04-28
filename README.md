# Student Names
Oliver Brown
Fernando Ortega
Madison Hinston 
Crook Campbell
Hugo Manrique-Pinell
Hannah Lydell

#  Seacobeck Curriculum Lab Web Application 

## Purpose
The Seacobeck Curriculum Lab Web Application works to improve the checkout and record keeping process for the Seacobeck Curriculum Lab team, moving them away from their previous Google Sheet/Forms system. This web application allows for borrowers to independently search for and checkout/return materials. Staff members (Admins and Student Workers) are able to keep track of checkouts, materials and their stats, logs and staff accounts. 

## Authors
The Seacobeck Curriculum lab system code was modified during the Spring 2026 semester, and works off the foundation of multiple semesters of work. The codebase's previous authors and contributions are listed unchanged below, with additional mention to the Whisky Valor team. The Seacobeck Curriculum lab's current implementation is mostly rewritten, but some foundational work remains such as the dbpersons database, login/logout systems, base CSS, etc. The team responsible for the Spring 2026 work includes Oliver Brown, Fernando Ortega, Madison Hinston, Crook Campbell, Hugo Manrique-Pinell, and Hannah Lydell.

## Previous Semester's Authors
The ODHS Medicine Tracker is based on an old open source project named "Homebase". [Homebase](https://a.link.will.go.here/) was originally developed for the Ronald McDonald Houses in Maine and Rhode Island by Oliver Radwan, Maxwell Palmer, Nolan McNair, Taylor Talmage, and Allen Tucker.

Modifications to the original Homebase code were made by the Fall 2022 semester's group of students. That team consisted of Jeremy Buechler, Rebecca Daniel, Luke Gentry, Christopher Herriott, Ryan Persinger, and Jennifer Wells.

A major overhaul to the existing system took place during the Spring 2023 semester, throwing out and restructuring many of the existing database tables. Very little original Homebase code remains. This team consisted of Lauren Knight, Zack Burnley, Matt Nguyen, Rishi Shankar, Alip Yalikun, and Tamra Arant. Every page and feature of the app was changed by this team.

The Gwyneth's Gifts VMS code was modified in the Fall of 2023, revamping the code into the present ODHS Medicine Tracker code. Many of the existing database tables were reused, and many other tables were added. Some portions of the software's functionality were reused from the Gwyneth's Gifts VMS code. Other functions were created to fill the needs of the ODHS Medicine Tracker. The team that made these modifications and changes consisted of Garrett Moore, Artis Hart, Riley Tugeau, Julia Barnes, Ryan Warren, and Collin Rugless.

The ODHS Medicine Tracker code was modified in the Fall of 2024, changing the code to the present Step VA Volunteer Management System code. Many existing database tables were reused or renamed, and some others were added. Some files and portions of the software's functionality were reused from ODHS Medicine Tracker, while other functions were created to fill the needs of Step VA Volunteer Management. The team which made changes and new addtions consisted of Ava Donley, Thomas Held, Madison McCarty, Noah Stafford, Jayden Wynes, Gary Young, and Imaad Qureshi.

In Spring 2025, the Step VA Volunteer Management code was adapted to develop the Fredericksburg SCPA Volunteer Management Web Application. Numerous existing database tables were retained with modifications or renamed, while new tables were introduced as needed. Certain files and functionalities from the original system were integrated, while additional features were designed specifically for the Fredericksburg SCPA Volunteer Management system. The team responsible for these updates and enhancements included Yalda Alemy, Luke Blair, Madison Van Buren, Sean Foley, Luke Gibson, Aiden Meyer, and Israel Ortiz.

## User Types
This system assumes 3 different types of users:
* Admins
* Student Workers
* Borrowers

Borrowers are able to independently search for and checkout/return materials. Student Workers have the ability to manage the material catalog, view checkouts and logs, import materials, and export checkouts. Admins are able to do all prior mentioned functions of a Student Worker, and additionally manage Worker accounts. 

The root admin account is:
Username: vmsroot
Password: vmsroot

## Features
Below is an in-depth list of system features:

* Staff Member Login/Logout
* Staff Member Dashboard
* Material Search
  * Filter/Sort Materials
* Self Service (Material Checkout/Return)
  * View up-to date Material information
  * Checkout Material
  * Return Material
  * Receive Confirmation Emails for Checkout/Return
  * Receive Status Reminders for Loans
* View/Export Checkouts
  * Export Checkout Report in Either .CSV or XLS format
* Import Materials
* View Materials
  * Delete/Mass Delete Materials
  * View Statistics about Materials
  * Add Materials
  * Edit Materials
* View Logs
  * Delete Logs
* View Workers
  * Create New Worker
  * Edit Worker
  * Delete Worker
* Change Password

## "localhost" Installation
Below are the steps required to run the project on your local machine for development and/or testing purposes. This installation procedure is updated from previous semester's steps to contain up-to date information specific to the Seacobeck Curriculum Lab Web Application.

1. [Download and install XAMPP](https://www.apachefriends.org/download.html)
2. Open a terminal/command prompt and change directory to your XAMPP install's htdocs folder
  * For Mac, the htdocs path is `/Applications/XAMPP/xamppfiles/htdocs`
  * For Ubuntu, the htdocs path is `/opt/lampp/htdocs/`
  * For Windows, the htdocs path is `C:\xampp\htdocs`
3. Clone the WellsProj repo into the htdocs folder from the github repo: 'https://github.com/obrown1353/WellsProj'
4. Start the MySQL server and Apache server via XAMPP
5. Open the PHPMyAdmin console by navigating to [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
6. Create a new database named `wellsdb` via the left sidepanel and open it. 
7. Import the `wellsdb.sql` file located in `WellsProj/sql` into this new database
8. Create a new user by navigating to `Privileges -> New -> Add user account`
9. Enter the following credentials for the new user:
  * Name: `wellsdb`
  * Password: `wellsdb`
  * Check Global Privileges 
10. Navigate to [http://localhost/WellsProj/](http://localhost/WellsProj/) 
11. Log into the root user account using the username `vmsroot` with password `vmsroot`

## Platform
Dr. Polack chose SiteGrounds as the platform on which to host the project. Below are some guides on how to manage the live project.

### SiteGround Dashboard
Access to the SiteGround Dashboard requires a SiteGround account with access. Access is managed by Dr. Polack.

### Localhost to Siteground
Follow these steps to transfter your localhost version of the Seacobeck Curriculum Lab code to Siteground. For a video tutorial on how to complete these steps, contact Dr. Polack. This installation procedure is updated from previous semester's steps to contain up-to date information specific to the Seacobeck Curriculum Lab Web Application.

1. Via Siteground's FileManager, access the public_html folder
2. Import all local host files directly into the public_html folder via the upload feature, ensuring index.php (and other files) are at the base of this folder
3. Create the following database-related credentials on Siteground under the MySQL tab (Note this information down for later steps):
  * Database - Create the database for the siteground version under the Databases tab in the MySQL Manager by selecting the 'Create Database' button. Database name is auto-generated and can be changed if you like.
  * User - Create a user for the database by either selecting the 'Create User' button under the Users tab, or by selecting the 'Add New User' button from the newly created database under the Databases tab. User name is auto-generated and can be changed  if you like.
  * Password - Created when user is created. Password is auto generated and can be changed if you like.
4. Assign the newly created user to the database via the User Management tab.
5. Access the newly created database by navigating to the PHPMyAdmin tab and selecting the 'Access PHPMyAdmin' button. This will redirect you to the PHPMyAdmin page for the database you just created. Navigate to the new database by selecting it from the database list on the left side of the page.
6. Select the 'Import' option from the database options at the top of the page. Select the 'Choose File' button and import the "wellsdb.sql" file from your software files.
  * If any changes are made to the structure of the databases locally, make sure the siteground databases are kept up-to date by dropping and reimporting the updated wellsdb.sql file
7. Navigate to the '/database/dbinfo.php' page via the Siteground FileManager. Inside the connect() function, assuming the server is "jenniferp236.sg-host.com" add the following lines above the line that starts with $con = ... replacing the information in brackets with the information from step 3. 

    if ($_SERVER['SERVER_NAME'] == 'jenniferp215.sg-host.com') {
        $user = '{User from step 3}';
        $database = '{Database from step 3}';
        $pass = '{Password from step 3}';
    }

### External Libraries and APIs
The Seacobeck Curriculum Lab System uses the PHPMailer library in order to automate the sending of loan status emails. All required files for this library are contained within the PHPMailer folder and should not need an additional download. However, if problems do arise with this 
version of PHPMailer, an updated version with additional installation steps can be accessed at the [PHPMailer Github Page](https://github.com/phpmailer/phpmailer). 

The given installation reccomendation of PHPMailer is via the PHP dependency manager [Composer](https://getcomposer.org) and can be installed with the command:
composer require phpmailer/phpmailer

### Potential Improvements
The following are potential improvements that can expanded on for the Seacobeck Curriculum Lab System at any later time. 
  * Forgot password
  * Mark optional and required fields for forms (e.g. add material)
  * Ensure email system actually works (overdue warning)
  * Material images (book cover retrieval)
  * Accessibility button to change font style and size
  * Bread crumbs → clickable links for a path

## License
The project remains under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl.txt).

## Acknowledgements
Thank you to Dr. Jennifer Polack and Dr. Melissa Wells for all the help with this system throughout this semester. Working for the Curriculum Lab has been a pleasure. 
