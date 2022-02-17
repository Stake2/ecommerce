<?php 

namespace Hcode\Model;

use Hcode\Model;

class Address extends Model {
	public function getValues() {
		return array(
			"des_address" => "test",
			"des_complement" => "test",
			"des_district" => "test",
			"des_city" => "test",
			"des_state" => "test",
			"des_country" => "test",
		);
	}
}

?>