<?php
class modules_egrappler {
	/**
	 * @brief The base URL to the datepicker module.  This will be correct whether it is in the 
	 * application modules directory or the xataface modules directory.
	 *
	 * @see getBaseURL()
	 */
	private $baseURL = null;
	/**
	 * @brief Returns the base URL to this module's directory.  Useful for including
	 * Javascripts and CSS.
	 *
	 */
	public function getBaseURL(){
		if ( !isset($this->baseURL) ){
			$this->baseURL = Dataface_ModuleTool::getInstance()->getModuleURL(__FILE__);
		}
		return $this->baseURL;
	}
	
	
	public function __construct(){
		$app = Dataface_Application::getInstance();
		$s = DIRECTORY_SEPARATOR;
		
		if ( isset($app->version) and $app->version >= 2 ){
			$app->registerEventListener('SkinTool.ready', array($this, 'registerSkin'), true);
		} else {
			df_register_skin('egrappler', dirname(__FILE__).$s.'templates');
		}
		$app->registerEventListener('filterTemplateContext', array($this, 'filterTemplateContext'));

		// Javascripts
		//$app->addHeadContent('<script src="'.htmlspecialchars(DATAFACE_URL.'/js/jquery.packed.js').'"></script>');

		// Styles & fonts
		//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($this->getBaseURL().'/css/bootstrap.min.css').'"/>');
		//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($this->getBaseURL().'/css/bootstrap-responsive.min.css').'"/>');
		//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600"/>');
		//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($this->getBaseURL().'/css/font-awesome.css').'"/>');
		//$app->addHeadContent('<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($this->getBaseURL().'/css/style.css').'"/>');

	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/jquery-1.7.2.min.js"></script>
	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/excanvas.min.js"></script>
	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/chart.min.js" type="text/javascript"></script>
	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/bootstrap.js"></script>
	//<script language="javascript" type="text/javascript" src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/full-calendar/fullcalendar.min.js"></script>
	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/base.js"></script>
	//<script src="{$ENV.DATAFACE_SITE_URL}/themes/responsive/js/signin.js"></script>


		$jt = Dataface_JavascriptTool::getInstance();
		$jt->addPath(dirname(__FILE__).$s.'js', $this->getBaseURL().'/js');
		$ct = Dataface_CSSTool::getInstance();
		$ct->addPath(dirname(__FILE__).$s.'css', $this->getBaseURL().'/css');

		$jt->import('jquery.packed.js');


	}

	public function registerSkin(){
		$s = DIRECTORY_SEPARATOR;
		df_register_skin('egrappler', dirname(__FILE__).$s.'templates');
	}
	
		
	public function filterTemplateContext($event){
		
		$event->context['egrappler'] = $this;
	}

	public function block__javascript_tool_includes(){

		//echo '<script src="'.htmlspecialchars($this->getBaseURL().'/js/bootstrap.js').'"></script>';
		//$app->addHeadContent('<script src="'.htmlspecialchars($this->getBaseURL().'/js/bootstrap.js').'"></script>');
		//echo '<script src="'.htmlspecialchars($this->getBaseURL().'/js/base.js').'"></script>';

	}

	/*function block__bread_crumbs(){

		import( dirname(__FILE__).'/inc/new_smarty_tags.php');
		echo cmc_bread_crumbs();
	}*/

	function block__result_list_content(){

		import( dirname(__FILE__).'/inc/cmc_ResultList.php');
		$app =& Dataface_Application::getInstance();
		$query = $app->getQuery();

		$list = new cmc_ResultList( $query['-table'], $app->db(), null, $query);
		echo $list->toHtml();

	}

	function block__result_controller(){

		import( dirname(__FILE__).'/inc/cmc_ResultController.php');
		$app =& Dataface_Application::getInstance();
		$query = $app->getQuery();

		$result_controller = new cmc_ResultController( $query['-table'], '', '', $query);
		echo $result_controller->toHtml();

	}

	function block__related_records_list(){

		import( dirname(__FILE__).'/inc/cmc_RelatedList.php');

		$app =& Dataface_Application::getInstance();
		$query =& $app->getQuery();
		$record =& $app->getRecord();

		if ( !$record ) {
			throw new Exception('No record found from which to form related list.', E_USER_ERROR);
		}

		if ( isset($query['-relationship']) ){
			$relationship = $query['-relationship'];
		} else {
			throw new Exception('No relationship specified for related list.', E_USER_ERROR);
		}

		$relatedList = new cmc_Dataface_RelatedList($record, $relationship);
		echo $relatedList->toHtml();

	}

	//function block__registration_form(){
		//echo "xxx";
		//return true;
	//}

}