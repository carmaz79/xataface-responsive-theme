<?php

import( 'Dataface/ResultController.php');

class cmc_ResultController extends Dataface_ResultController {

    function listHtml($prefix = '') {
        $app = & Dataface_Application::getInstance();
        $rs = & $this->_resultSet;
        $pages = array();
        $start = $rs->start();
        $end = $rs->end();
        $limit = max($rs->limit(), 1);
        $found = $rs->found();

        // we show up to 5 pages on either side of the current position
        $pages_before = ceil(floatval($start) / floatval($limit));
        $pages_after = ceil(floatval($found - $end - 1) / floatval($limit));
        $curr_page = $pages_before + 1;
        $total_pages = $pages_before + $pages_after + 1;

        //$i = $curr_page;
        $i_start = $start;
        for ($i = $curr_page; $i > max(0, $curr_page - 5); $i--) {
            $pages[$i] = $app->url('-' . $prefix . 'limit=' . $limit . '&-' . $prefix . 'skip=' . max($i_start, 0));
            if ($this->_baseUrl)
                $pages[$i] = $this->_baseUrl . '?' . substr($pages[$i], strpos($pages[$i], '?') + 1);
            $i_start -= $limit;
        }
        //$i = $curr_page+1;
        $i_start = $start + $limit;
        for ($i = $curr_page + 1; $i <= min($total_pages, $curr_page + 5); $i++) {
            $pages[$i] = $app->url('-' . $prefix . 'limit=' . $limit . '&-' . $prefix . 'skip=' . $i_start);
            if ($this->_baseUrl)
                $pages[$i] = $this->_baseUrl . '?' . substr($pages[$i], strpos($pages[$i], '?') + 1);
            $i_start += $limit;
        }
        ksort($pages);

        $pages2 = array();
        if ($curr_page > 1) {
            $pages2[df_translate('scripts.GLOBAL.LABEL_PREV', 'Prev')] = $pages[$curr_page - 1];
        }

        foreach ($pages as $pageno => $pageval) {
            $pages2[$pageno] = $pageval;
        }

        if ($curr_page < $total_pages) {

            $pages2[df_translate('scripts.GLOBAL.LABEL_NEXT', 'Next')] = $pages[$curr_page + 1];
        }
        $appurl = $app->url('');
        $appurl = preg_replace('/[&\?]' . preg_quote('-' . $prefix . 'limit=') . '[^&]*/', '', $appurl);
        $appurl = preg_replace('/[&\?]' . preg_quote('-' . $prefix . 'skip=') . '[^&]*/', '', $appurl);
        $urlprefix = ( $this->_baseUrl ? $this->_baseUrl . '?' . substr($appurl, strpos($appurl, '?') + 1) : $appurl);

		$out = array('<div class="pagination"><h6 class="pull-left">');
        $out[] = '' . df_translate('scripts.GLOBAL.MESSAGE_FOUND', 'Found ' . $found . ' records', array('found' => $found)) . '.';
        $out[] = '' . df_translate('scripts.GLOBAL.LABEL_SHOWING', 'Showing') . ' <input style="width: inherit; text-align: center;" type="text" value="' . $limit . '" onchange="window.location = \'' . $urlprefix . '&-' . $prefix . 'limit=\'+this.value" size="1"/>' . df_translate('scripts.GLOBAL.MESSAGE_RESULTS_PER_PAGE', 'Results per page').'</h6>';
		if (count($pages2) > 1) {
		$out[] = '<ul class="pull-right">';
        foreach ($pages2 as $pageno => $link) {
            if ($pageno == $curr_page)
                $selected = ' active';
            else
                $selected = '';
            $out[] = '<li class="'.$selected.'"><a href="' . df_escape($link) . '" class="paginate_button' . $selected . '">' . $pageno . '</a></li>';
        }
        $out[] = '</ul>';
		}
        $out[] = '</div>';

        return implode("\n", $out);
    }

    function browseHtml($prefix) {



        $html = '
   			<div class="resultController">
   			
   			<div class="container"><div class="controllerJumpMenu">
   			<b>Jump: </b>';
        $html .= $this->jumpMenu() . '</div>
   			
   			</div>
   			
   			
   			<table class="forwardBackTable" width="100%" border="0" cellpadding="0" cellspacing="5"><tr><td width="33%" valign="top" align="left" bgcolor="#eaeaea">
   			' . $this->getPrevLinkHtml() . '
   			</td><td width="34%" valign="top" align="center">
   			
   			' . $this->getCurrentHtml();

        if ($this->_query['-mode'] == 'list') {
            $html .='<br/>' . $this->limitField();
        }

        $html .= '
			
   			</td><td width="33%" valign="top" align="right" bgcolor="#eaeaea">
   			
   			' . $this->getNextLinkHtml() . '
   			</td>
   			</tr>
   			</table>
   			</div>
   				';
        return $html;
    }

}