<?php

	function cmc_bread_crumbs(){
		$base = null;
		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
		if ( $query['-mode'] === 'browse' and $query['-action'] != 'new'){
			$record =& $app->getRecord();
			$base = '';
			if ( $record ){
				foreach ( $record->getBreadCrumbs() as $label=>$url){
					$base .= ' <li><a href="'.$url.'" id="bread-crumbs-'.str_replace(' ','_', $label).'">'.$label.'</a></li>';
				}
			}
			$base = substr($base, 0);

		} 

		$del = Dataface_Application::getInstance()->getDelegate();
		if ( !$base and $del and method_exists($del, 'getBreadCrumbs') ){
			$bc = $del->getBreadCrumbs();
			if ($bc ){
				$base = '';

				foreach ( $bc as $label=>$url){
					$base .= ' <li><a href="'.$url.'" id="bread-crumbs-'.str_replace(' ','_', $label).'">'.$label.'</a></li>';
				}
			}
		}
		if ( !$base ){
			$table =& Dataface_Table::loadTable($query['-table']);
			$base = '<li> <a href="#">'.$table->getLabel().'</a> </li>';
		}
		
		
		
		$action =& $app->getAction();
		if ( PEAR::isError($action) ){
			return '';
		}
		$base .= ' <li class="active"> '.Dataface_LanguageTool::translate(
			$action['label_i18n'],
			$action['label'])."</li>";
		return "<li><b>".df_translate('scripts.Dataface_SkinTool.LABEL_BREADCRUMB', "You are here")."</b></li>".$base."";
	}
