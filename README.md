# xataface-theme_responsive-module
A xataface theme module with responsive technology

This theme is based on http://www.egrappler.com/bootstrap-responsive-admin-template/index.html

For use uncompress egrappler.zip file to modules directory created on your application directory. Then on your conf.ini file add the following:

disable_g2 = 1 ;;Necessary for proper working

[_modules]
modules_egrappler=modules/egrappler/egrappler.php


For configuration:

Supports two menus (vertical and horizontal) as original xataface theme

[_prefs]

horizontal_tables_menu = 1 ;; 0/1 values


You can add icons to your menu. Add this new section to your conf.ini 

[_icons] ;; Is a new section on conf.ini

table1_name = "icon-glass"
table2_name = "icon-facetime-video"
table3_name = "icon-shopping-cart"

You can choose from all these icons: http://www.egrappler.com/bootstrap-responsive-admin-template/icons.html
