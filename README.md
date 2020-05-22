# php-excel
This is PHP form look a like Excel. It has insert, update and delete functionality available.

By default it will have 5 balnk rows always in the bottom.\
New blank row can be added by clicking on the button 'Add Row'\
If user reaches to the end of the page, it will automatically append new blank row\

Currently it has 3 columns. Follw these steps to add new columns\
Step 1: First add required columns in database table\
Step 2: On line 22, add new columns to php array $cols\
Step 3: Add new "th" in the html table\
Step 4: Add new "td" in JS fuunction at line 123
