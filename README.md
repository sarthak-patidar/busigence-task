**DATA ANALYZER**

**Getting Started**

* Pull the source code from GitHub via Terminal or download the zip.
* Open the inc/globals.php
    * Change Database Config Variables
    * Create a database named "busigence".
    * Start the server by typing the command:
       `php -S 127.0.0.1:8080`
    * Open browser and visit `127.0.0.1:8080`
    
    
**Work Flow**

1. *Source Selector*
    * Select CSV and upload a csv file.
    * Upon uploading this data gets imported into local `busigence` database with table name same as name of the file.
    
    * Select MySQL and enter the login credentials of the DB HOST.
    * Upon successful login, this will load the Database Schema.
    
    
2. *Format Selector*
    * Once you upload a csv file, it will be shown here.
    
    * After successful login, you can see all the databases and their tables in this panel.
    
    
3. *Visualizer*
    * On Clicking run the mapping button, it will create a tabular view of the csv file which was imported into the database earlier.
    * Once the view is created, you can view it by clicking on the `slider button` in the navbar.
    
    * Select any `2 tables` you wish to merge and sort by dragging and dropping the table names from the format `selector panel`.
    * Once both the tables are selected, you can select the `primary keys` of the tables on which you want to perform the join operation.
    * Now, you need to select the `type of sorting` you need and on which `field/column`.
    * Once you are done, select the output file type, you need.
        * If you select `CSV`, it will download Filtered data as a csv file, named as `table1_table2_PK_sortField_sortType`.
        * If you select `MySQL`, it will import this filtered data into a new MySQL table, named as `table1_table2_PK_sortField_sortType`.
    * You can view the filtered data in a `tabular format` by clicking on the `slider button` in the navbar.