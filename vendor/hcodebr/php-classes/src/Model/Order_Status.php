<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order_Status extends Model {
	const EM_ABERTO = 1;
	const AGUARDANDO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;

	public static function List_All() {
		$sql = new Sql();

		return $results = $sql -> select("SELECT * FROM tb_ordersstatus ORDER BY des_status");
	}
}

?>