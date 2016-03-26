# xataface-responsive-theme
A xataface theme with responsive technology

This theme is based on http://www.egrappler.com/bootstrap-responsive-admin-template/index.html

For use uncompress responsive.zip file to a directory (your theme name) created on your application theme directory. Then on your conf.ini file add the following:
[_themes]
your_theme_name=themes/your_theme_name

For proper operation, you must make these changes to the source code Xataface:

1- /xataface/Dataface/RelatedList.php

	Add class to table: 
                        <table class="table table-striped table-bordered listing relatedList relatedList--' . $this->_tablename . ' relatedList--' . $this->_tablename . '--' . $this->_relationship_name . '" id="relatedList"> 

2- /xataface/Dataface/ResultList.php

	Add classes to table: 
		<table data-xataface-query="'.df_escape($sq).'" id="result_list" class="table table-striped table-bordered listing resultList resultList--'.$this->_tablename.'">
	Add class to with selected actions
		<li class="btn" id="action-{$action['id']}"><a href="{$action['url']}" onclick="{$action['onclick']}" title="{$action['description']}">{$img}{$action['label']}</a></li>

3- actions.ini (can be our application file)
	Add:

[logout > logout] 
	url="{$site_href}?-action=logout" 
	condition="df_is_logged_in()" 
	label="Log Out" 
	description="Log out of the system" 
	category=personal_tools 
	order=100 
	 
[login > login] 
	url="{$site_href}?-action=login" 
	condition="!df_is_logged_in() and @$this->_conf['_auth']" 
	label="Log In" 
	description="Log into the system" 
	category=top_right_menu_bar 

4- /xataface/Dataface/LanguageTool.php

	Add class to ul:

                        echo '<ul id="'.df_escape($name).'" class="language-selection-list dropdown-menu"> 

5- You can add icons to your menu. Add this new section to your conf.ini 

[_icons] ;; Is a new section on conf.ini

table1_name = "icon-glass"
table2_name = "icon-glass"
table3_name = "icon-glass"

You can choose from all these icons: http://www.egrappler.com/bootstrap-responsive-admin-template/icons.html
