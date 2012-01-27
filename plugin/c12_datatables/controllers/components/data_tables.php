<?php
/**
 * jQuery DataTables Component.
 *
 *
 * PHP versions 5 - Known & Tested
 *
 * Copyright 2011, Simon Dann. (http://likepie.net)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, Simon Dann. (http://likepie.net)
 * @link          http://likepie.net/cakephp-jquery-datatables-componenthelper/ jQuery DataTables Component Project
 * @package       CakePHP-Datatables   
 * @since         CakePHP-Datatables v 1.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class DataTablesComponent extends Object {

	var $sLimit = null;
	var $sNames = null;
	var $sFields = null;
	var $model = null;
	var $conditions = null;

   /**
	* initialize function.
	* loads any and all linked models to the controller, this is quite inefficient and dumb atm
	* very lazy and should be fixed once someone comes up with a better solution.
	* @since     CakePHP-Datatables v 1.0.0
	*/
	function initialize(&$controller, $settings = array()) {
		$this->controller =& $controller;
		foreach ($this->controller->modelNames as $modelClass){
			$controller->loadModel($modelClass);
			$this->$modelClass = $controller->$modelClass;
		}
	}
	
	function output ($dataTables = null){
			
			$this->conditions = (isset($dataTables['conditions']))?$dataTables['conditions']:null;
	
		// A way of getting the names of all the columns.
			if ( isset( $this->controller->params['url']['sColumns'] ) ){
				$this->sNames = explode(',', $this->controller->params['url']['sColumns'] );
				$key = array_search('buttons', $this->sNames);
				if($key){
					unset($this->sNames[$key]);
				}
				
				// get table fields
				foreach ($dataTables['aColumns'] as $key=>$col){
					if($key!='buttons')
						$this->sFields[] = $col['sField'];
				}
				
			}
			
			// Are we sorting columns
			if ( isset( $this->controller->params['url']['iSortCol_0'] ) ){
				for ( $i=0 ; $i<intval( $this->controller->params['url']['iSortingCols'] ) ; $i++ ){
					if ( $this->controller->params['url'][ 'bSortable_'.intval( $this->controller->params['url']['iSortCol_'.$i] ) ] == "true" ){
						if (isset ($this->sFields)){
							//$ordering[] = $dataTables['use'] . '.' . $this->sNames[ intval( $this->controller->params['url']['iSortCol_'.$i] ) ] . ' ' . $this->controller->params['url']['sSortDir_'.$i];
							$ordering[] =  $this->sFields[ intval( $this->controller->params['url']['iSortCol_'.$i] ) ] . ' ' . $this->controller->params['url']['sSortDir_'.$i];
						}
					}
				}
			}
			
			// If the user is searching
			if(isset($this->controller->params['url']['sSearch'])){
				if ( $this->controller->params['url']['sSearch'] != "" ){
					for ( $i=0 ; $i<count($this->sFields) ; $i++ ){
						if ( $this->controller->params['url']['bSearchable_'.$i] == "true"){
							$search_conditions[$this->sFields[$i] . ' LIKE']  = '%' . $this->controller->params['url']['sSearch'] . '%';
						}
					}
				}
			}
			if (isset($search_conditions) && !empty($search_conditions)){
				$this->conditions['and'] = array('or'=>$search_conditions);
			}
			
			$n = array(
				'conditions' => $this->conditions,
				'recursive' => 1,
				'fields' => $this->sFields,
				'order' => (isset($ordering))?$ordering:'',
				'limit' => (isset($this->controller->params['url']['iDisplayLength']))?$this->controller->params['url']['iDisplayLength']:20, 
				'offset'=> (isset($this->controller->params['url']['iDisplayStart']))?$this->controller->params['url']['iDisplayStart']:0, 
			);
			
			
			$rResult = $this->$dataTables['use']->find('all', $n);
			$iTotal = $this->$dataTables['use']->find('count');
			
			if (isset($this->controller->params['url']['sSearch'])){
				$n['limit'] = null;
				$n['offset'] = null;
				$iFilteredTotal = $this->$dataTables['use']->find('count', array('conditions' => $this->conditions));
			}else{
				$iFilteredTotal = $iTotal;
			}
			
			$output = array(
				"aoColumns" => implode(',', $this->sNames),
				"sEcho" => (isset($this->controller->params['url']['sEcho']))?intval($this->controller->params['url']['sEcho']):'',
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iFilteredTotal,
				"aaData" => array()
			);
			
			foreach ($rResult as $record){
				$row = array();
				$ts = '';
				for ( $i=0 ; $i<count($this->sFields) ; $i++ ){
					//$row[] = $this->filter($this->sNames[$i], $record, $dataTables['use']);
					$fData = explode('.',$this->sFields[$i]);
					$row[] = $record[$fData[0]][$fData[1]];
				}
				
				/* Add buttons as an additional column?
				 * maybe in the form of:
				 * $dataTables['buttons'] = array(
				 * 		'view' => '/view/%id%',
				 *		'edit' => '/edit/%id$',
				 * );
				 * with urls being able to be hooked into cakes automagic black box :)
				 */
				$output['aaData'][] = $row;
			}
			
			echo json_encode( $output );
			die();
			
	}

}

?>
