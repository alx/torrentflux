<?php
		##################################
		## TORRENTFLUX LOADAVG OSX HACK 
		##################################
		## MADE BY COUSINCOCAINE
		## aka
		## Dr.Apple
		##################################
		// $/usr/bin/uptime
		// 10:00  up 87 days, 15:56, 3 users, load averages: 0.20 0.23 0.24
	      $uptime_array = explode("load averages:", shell_exec('uptime'));
             $loadavg_array = explode(" ",trim($uptime_array[1]));
		?>
