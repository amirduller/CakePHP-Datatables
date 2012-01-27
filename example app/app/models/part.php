<?php
class Part extends AppModel {
	var $name = 'Part';
	var $displayField = 'name';
	
	var $belongsTo = array(
		'Tag' => array(
			'className' => 'Tag',
			'foreignKey' => 'tag_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
}
