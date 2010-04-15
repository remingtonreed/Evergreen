<?php

class Client_Model extends Model {

	public function __construct() {
		$this->setTableName('clients');
        
        $this->addField('id', array('key'));
        $this->addField('name', array('required'));
        $this->addField('website');
        $this->addField('active');
	}

}

?>