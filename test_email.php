<?php
try {
	mail("k.sutassananon@gmail.com", "Test PHP Emailing", "Test");
	echo "Email supposedly sent";
} catch(Exception $e) {
	echo $e->getMessage();
}
?>