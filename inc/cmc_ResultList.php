<?php

import( 'Dataface/ResultList.php');

class cmc_ResultList extends Dataface_ResultList {

 	function toHtml(){
 		$app =& Dataface_Application::getInstance();
 		$query =& $app->getQuery();
 		if ( isset( $query['-sort']) ){
 			$sortcols = explode(',', trim($query['-sort']));
 			$sort_columns = array();
 			foreach ($sortcols as $sortcol){
 				$sortcol = trim($sortcol);
 				if (strlen($sortcol) === 0 ) continue;
 				$sortcol = explode(' ', $sortcol);
 				if ( count($sortcol) > 1 ){
 					$sort_columns[$sortcol[0]] = strtolower($sortcol[1]);
 				} else {
 					$sort_columns[$sortcol[0]] = 'asc';
 				}
 				break;
 			}
 			unset($sortcols);	// this was just a temp array so we get rid of it here
 		} else {
 			$sort_columns = array();
 		}
 		
 		// $sort_columns should now be of the form [ColumnName] -> [Direction]
 		// where Direction is "asc" or "desc"
 		
 		
 		
 		if ( $this->_resultSet->found() > 0 ) {
 		
 			
 			if ( @$app->prefs['use_old_resultlist_controller'] ){
				ob_start();
				df_display(array(), 'Dataface_ResultListController.html');
				$controller = ob_get_contents();
				ob_end_clean();
			}
		
 			
			ob_start();
			//echo '<div style="clear: both"/>';
			if ( !defined('Dataface_ResultList_Javascript') ){
				define('Dataface_ResultList_Javascript',true);
				$jt = Dataface_JavascriptTool::getInstance();
				$jt->import('Dataface/ResultList.js');
				
				//echo '<script language="javascript" type="text/javascript" src="'.DATAFACE_URL.'/js/Dataface/ResultList.js"></script>';
			}
			
			if ( !@$app->prefs['hide_result_filters'] and count($this->_filterCols) > 0 ){
				echo $this->getResultFilters();
			}
			unset($query);
			
			if ( @$app->prefs['use_old_resultlist_controller'] ){
				echo '<div class="resultlist-controller" id="resultlist-controller-top">';
	
				echo $controller;
				echo "</div>";
			}
		
			
			
			$canSelect = false;
			if ( !@$app->prefs['disable_select_rows'] ){
				$canSelect = Dataface_PermissionsTool::checkPermission('select_rows',
							Dataface_PermissionsTool::getPermissions( $this->_table ));
			}
			
			
			$sq = $myq = $app->getQuery();
			foreach ($sq as $sqk=>$sqv ){
				if ( !$sqk or $sqk{0} == '-' ){
					unset($sq[$sqk]);
				}
			}
			if ( @$myq['-sort'] ) $sq['-sort'] = $myq['-sort'];
			if ( @$myq['-skip'] ) $sq['-skip'] = $myq['-skip'];
			if ( @$myq['-limit'] ) $sq['-limit'] = $myq['-limit'];
			
			
			$sq = json_encode($sq);
			$jt = Dataface_JavascriptTool::getInstance();
			$jt->import('list.js');
			echo '
				<table data-xataface-query="'.df_escape($sq).'" id="result_list" class="table table-striped table-bordered listing resultList resultList--'.$this->_tablename.'">
				<thead>
				<tr>';
			if ( $canSelect){
				echo '<th><input type="checkbox" onchange="toggleSelectedRows(this,\'result_list\');"></th>';
			}
			
			if ( !@$app->prefs['disable_ajax_record_details']  ){
				echo '	<th><!-- Expand record column --></th>
				';
			}
			echo '<th class="row-actions-header"></th>';
			$results =& $this->getResults();
			$perms = array();
			
			
			foreach ($this->_columns as $key){
				$cursor=$this->_resultSet->start();
				$results->reset();
				$perms[$key] = false;
				while ( $results->hasNext() ){
					$record = $results->next();
					if ( $record->checkPermission('list', array("field"=>$key)) ){
						$perms[$key] = true;
						break;
					}
				}
			}
			
			$numCols = 0;
			
			$rowHeaderHtml = $this->renderRowHeader();
			if ( isset($rowHeaderHtml) ){
				echo $rowHeaderHtml;
			} else {
				
				
				
				foreach ($this->_columns as $key ){
					if ( in_array($key, $this->_columns) ){
						//if ( !($perms[$key] =  Dataface_PermissionsTool::checkPermission('list', $this->_table, array('field'=>$key)) /*Dataface_PermissionsTool::view($this->_table, array('field'=>$key))*/) ) continue;
						if ( !@$perms[$key] ) continue;
						if ( isset($sort_columns[$key]) ){
							$class = 'sorted-column-'.$sort_columns[$key];
							$query = array();
							$qs_columns = $sort_columns;
							unset($qs_columns[$key]);
							$sort_query = $key.' '.($sort_columns[$key] == 'desc' ? 'asc' : 'desc');
							foreach ( $qs_columns as $qcolkey=> $qcolvalue){
								$sort_query .= ', '.$qcolkey.' '.$qcolvalue;
							}
						} else {
							$class = 'unsorted-column';
							$sort_query = $key.' asc';
							foreach ( $sort_columns as $scolkey=>$scolvalue){
								$sort_query .= ', '.$scolkey.' '.$scolvalue;
							}
							
						}
						$sq = array('-sort'=>$sort_query);
						$link = Dataface_LinkTool::buildLink($sq);
						$numCols++;
						$label = $this->_table->getFieldProperty('column:label', $key);
						$legend = $this->_table->getFieldProperty('column:legend', $key);
						if ( $legend ){
							$legend = '<span class="column-legend">'.df_escape($legend).'</span>';
						}
						
						$colType = $this->_table->getType($key);
						$class .= ' coltype-'.$colType;
						$cperms = $this->_table->getPermissions(array('field'=>$key));
						if ( !$this->_table->isSearchable($key) or !@$cperms['find'] ){
							$class .= ' unsearchable-column';
						} else {
							$class .= ' searchable-column';
						}
						
						$class .= ' '.$this->getHeaderCellClass($key);
						
						if ( !$label ) $label = $this->_table->getFieldProperty('widget:label',$key);
						$searchColumn = $this->_table->getDisplayField($key);
						echo "<th data-column=\"$key\" data-search-column=\"$searchColumn\" class=\"$class\"><a href=\"$link\">".df_escape($label)."</a> $legend</th>";
					}
				}
			}
			echo "</tr>
				</thead>
                                <tfoot style='display:none'>".$this->getTfootContent()."</tfoot>
				<tbody>
				";
	
			
			$cursor=$this->_resultSet->start();
			$results->reset();
			$baseQuery = array();
			foreach ( $_GET as $key=>$value){
				if ( strpos($key,'-') !== 0 ){
					$baseQuery[$key] = $value;
				}
			}
			$evenRow = false;
			while ($results->hasNext() ){
				$rowClass = $evenRow ? 'even' : 'odd';
				$evenRow = !$evenRow;
				$record =& $results->next();
				$recperms = $record->getPermissions();
				
				if ( !@$recperms['view'] ){
					$cursor++;
					unset($record);
					continue;
				}
				$rowClass .= ' '.$this->getRowClass($record);
				
				
				
				$query = array_merge( $baseQuery, array( "-action"=>"browse", "-relationship"=>null, "-cursor"=>$cursor++) );
				
				if (  @$recperms['link'] ){
					if ( @$app->prefs['result_list_use_geturl'] ){
						$link = $record->getURL('-action=view');
					} else {
						
						$link = Dataface_LinkTool::buildLink($query).'&-recordid='.urlencode($record->getId());
					}
				} else {
					$del =& $record->_table->getDelegate();
					if ( $del and method_exists($del, 'no_access_link') ){
						$link = $del->no_access_link($record);
					} else {
						$link = null;
					}
				}
				$recordid = $record->getId();
				
				
				echo "<tr class=\"listing $rowClass\">";
				if ( $canSelect ) {
					$permStr = array();
					foreach ($recperms as $pk=>$pv){
						if ( $pv ) $permStr[] = $pk;
					}
					$permStr = df_escape(implode(',', $permStr));
					echo '<td class="checkbox-cell"><input class="rowSelectorCheckbox" xf-record-id="'.df_escape($recordid).'" id="rowSelectorCheckbox:'.df_escape($recordid).'" type="checkbox" data-xf-permissions="'.$permStr.'"></td>';
				}
				
				
				
				
				if ( !@$app->prefs['disable_ajax_record_details']  ){
					echo '<td class="ajax-record-details-cell">';
					echo '<script language="javascript" type="text/javascript"><!--
							registerRecord(\''.addslashes($recordid).'\',  '.$record->toJS(array()).');
							//--></script>
							<img src="'.DATAFACE_URL.'/images/treeCollapsed.gif" onclick="resultList.showRecordDetails(this, \''.addslashes($recordid).'\')"/>';
					
					
					echo '</td>';
					unset($at, $actions);
				}
				
				$at =& Dataface_ActionTool::getInstance();
				$actions = $at->getActions(array('category'=>'list_row_actions', 'record'=>&$record));
				//print_r($actions);
				echo '<td class="row-actions-cell">';
				if ( count($actions)>0){
					echo ' <span class="row-actions">';
					foreach ($actions as $action){
						echo '<a href="'.df_escape($action['url']).'" class="'.df_escape($action['class']).' '.(@$action['icon']?'with-icon':'').'" '.(@$action['icon']?' style="'.df_escape('background-image: url('.$action['icon'].')').'"':'').(@$action['target']?' target="'.df_escape($action['target']).'"':'').' title="'.df_escape(@$action['description']?$action['description']:$action['label']).'"><span>'.df_escape($action['label']).'</span></a> ';
					}
					echo '</span>';
				}
				echo '</td>';
				
				
				
				$rowContentHtml = $this->renderRow($record);
				if ( isset($rowContentHtml) ){
					echo $rowContentHtml;
				} else {
					//$expandTree=false; // flag to indicate when we added the expandTree button
					//if ( @$app->prefs['enable_ajax_record_details'] === 0 ){
					//	$expandTree = true;
					//}
					
					foreach ($this->_columns as $key){
						$thisField =& $record->_table->getField($key);
						if ( !$perms[$key] ) continue;
						
						$val = $this->renderCell($record, $key);
						if ( $record->checkPermission('edit', array('field'=>$key)) and !$record->_table->isMetaField($key)){
							$editable_class = 'df__editable_wrapper';
						} else {
							$editable_class = '';
						}
						
						if ( !@$thisField['noLinkFromListView'] and $link and $val ){
							$val = "<a href=\"$link\" class=\"unmarked_link\">".$val."</a>";
							$editable_class = '';
						} else {
							
						}
						
						if ( @$thisField['noEditInListView'] ) $editable_class='';
						
						
						$cellClass = 'resultListCell resultListCell--'.$key;
						$cellClass .= ' '.$record->table()->getType($key);
						if ( !trim($val) ){
						    $val = '&nbsp;';
						}
						echo "<td id=\"td-".rand()."\" class=\"field-content $cellClass $rowClass $editable_class\">$val</td>";
						unset($thisField);
					}
				}
				echo "</tr>";
				
				echo "<tr class=\"listing $rowClass\" style=\"display:none\" id=\"{$recordid}-row\">";
				if ( $canSelect ){
					echo "<td><!--placeholder for checkbox col --></td>";
				}
				echo '<td><!-- placeholder for actions --></td>';
				echo "<td colspan=\"".($numCols+1)."\" id=\"{$recordid}-cell\"></td>
					  </tr>";
				
				unset($record);
			}
			if ( @$app->prefs['enable_resultlist_add_row'] ){
				echo "<tr id=\"add-new-row\" df:table=\"".df_escape($this->_table->tablename)."\">";
				if ( $canSelect ) $colspan=2;
				else $colspan = 1;
				echo "<td colspan=\"$colspan\"><script language=\"javascript\">require(DATAFACE_URL+'/js/addable.js')</script><a href=\"#\" onclick=\"df_addNew('add-new-row');return false;\">".df_translate('scripts.GLOBAL.LABEL_ADD_ROW', "Add Row")."</a></td>";
				foreach ( $this->_columns as $key ){
					echo "<td><span df:field=\"".df_escape($key)."\"></span></td>";
				}
				echo "</tr>";
			}
			echo "</tbody>
				</table>";
			if ( $canSelect ){
				echo  '<form id="result_list_selected_items_form" method="post" action="'.df_absolute_url(DATAFACE_SITE_HREF).'">';
				$app =& Dataface_Application::getInstance();
				$q =& $app->getQuery();
				foreach ( $q as $key=>$val){
					if ( strlen($key)>1 and $key{0} == '-' and $key{1} == '-' ){
						continue;
					}
					echo '<input type="hidden" name="'.urlencode($key).'" value="'.df_escape($val).'" />';
				}
				echo '<input type="hidden" name="--selected-ids" id="--selected-ids" />';
				echo '<input type="hidden" name="-from" id="-from" value="'.$q['-action'].'" />';
				echo '<input type="hidden" name="--redirect" value="'.base64_encode($app->url('')).'" />';
				echo '</form>';

	
				import('Dataface/ActionTool.php');
				$at =& Dataface_ActionTool::getInstance();
				$actions = $at->getActions(array('category'=>'selected_result_actions'));
				if ( count($actions) > 0){
					echo '<div id="selected-actions" class="form-group"><div class="col-md-12" id="result_list-selectedActionsMenu"><p>'.df_translate('scripts.Dataface_ResultList.MESSAGE_WITH_SELECTED', "With Selected").': </p>';
					foreach ($actions as $action){
						$img = '';
						if ( @$action['awicon'] ){
							$img = '<i class="'.$action['awicon'].'"></i>';
						}
						else $img = '<i class="icon-magic"></i>';
						if ( @$action['class'] ){
							$action_class = $action['class'];
						}
						else $action_class = 'btn btn-default';

						if ( !@$action['onclick'] and !$action['url'] ){
							$action['onclick'] = "return actOnSelected('result_list', '".@$action['name']."'".(@$action['confirm']?", function(){return confirm('".addslashes($action['confirm'])."');}":"").")";

						}

						echo <<<END
						<a class="btn btn-mini {$action_class}" href="{$action['url']}" onclick="{$action['onclick']}" title="{$action['description']}">{$img}{$action['label']}</a>
END;
					}


					echo '</div></div>';
				}
			}
		
			if ( @$app->prefs['use_old_resultlist_controller'] ){
				echo '<div class="resultlist-controller" id="resultlist-controller-bottom">';
	
				echo $controller;
				echo '</div>';
			}
		
			
			$out = ob_get_contents();
			ob_end_clean();
		} else {
			if ( @$app->prefs['use_old_resultlist_controller'] ){
				ob_start();
				df_display(array(), 'Dataface_ResultListController.html');
				$out = ob_get_contents();
				ob_end_clean();
			} else {
				$out = '';
			}
			$out .= "<p style=\"clear:both\">".df_translate('scripts.GLOBAL.MESSAGE_NO_MATCH', "No records matched your request.")."</p>";
		}
 		
 		return $out;
 	}
	
 	function getResultFilters(){
                if ( !$this->_filterCols ){
                    return '';
                }
 		ob_start();
 		$app =& Dataface_Application::getInstance();
 		$query =& $app->getQuery();

		echo '<div class="dataTables_length">
		<h3>'.df_translate('scripts.Dataface_ResultList.MESSAGE_FILTER_RESULTS', 'Filter Results').':</h3>
		<script language="javascript"><!--

		function resultlist__updateFilters(col,select){
			var currentURL = "'.$app->url('').'";
			var currentParts = currentURL.split("?");
			var currentQuery = "?"+currentParts[1];
			var value = select.options[select.selectedIndex].value;
			var regex = new RegExp(\'([?&])\'+col+\'={1,2}[^&]*\');
			if ( currentQuery.match(regex) ){
				if ( value ){
					prefix = "=";
				} else {
					prefix = "";
				}
				currentQuery = currentQuery.replace(regex, \'$1\'+col+\'=\'+prefix+encodeURIComponent(value));
			} else {
				currentQuery += \'&\'+col+\'==\'+encodeURIComponent(value);
			}
			currentQuery = currentQuery.replace(/([&\?])-skip=[^&]+/, "$1");
			window.location=currentParts[0]+currentQuery;
		}
		//--></script>
		';

		$qb = new Dataface_QueryBuilder($this->_table->tablename, $query);
		foreach ( $this->_filterCols as $col ){
			$field =& $this->_table->getField($col);

			unset($vocab);
			if ( isset($field['vocabulary']) ){
				$vocab =& $this->_table->getValuelist($field['vocabulary']);

			} else {
				$vocab=null;

			}

			echo '<label class="list-filter"> '.df_escape($field['widget']['label']).' <select class="form-control"  onchange="resultlist__updateFilters(\''.addslashes($col).'\', this);"><option value="">'.df_translate('scripts.GLOBAL.LABEL_ALL', 'All').'</option>';

			$res = df_query("select `$col`, count(*) as `num` ".$qb->_from()." ".$qb->_secure( $qb->_where(array($col=>null)) )." group by `$col` order by `$col`", null, true);
			if ( !$res and !is_array($res)) trigger_error(xf_db_error(df_db()), E_USER_ERROR);
			if ( @$query[$col] and $query[$col]{0} == '=' ) $queryColVal = substr($query[$col],1);

			else $queryColVal = @$query[$col];

			//while ( $row = xf_db_fetch_assoc($res) ){
			foreach ($res as $row){
				if ( isset($vocab) and isset($vocab[$row[$col]]) ){
					$val = $vocab[$row[$col]];
				} else {
					$val = $row[$col];
				}

				if ( $queryColVal == $row[$col] ) $selected = ' selected';
				else $selected = '';
				echo '<option value="'.df_escape($row[$col]).'"'.$selected.'>'.df_escape($val).' ('.$row['num'].')</option>';

			}
			//@xf_db_free_result($res);
			echo '</select></label>';
		}
		echo '</div>';
		$out = ob_get_contents();
		ob_end_clean();
		return $out;


 	}

}