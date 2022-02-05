<?php 

function formatPrice(float $vl_price) {
	return number_format($vl_price, 2, ",", ".");
}

?>