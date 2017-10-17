			<div data-role="header" data-theme="a">
				
			</div>
			<div role="main" class="ui-content">
				<div class="power-control">
					<h1><?php echo $device_info[0]['Name']; ?></h1>
					<img src="/assets/img/<?php echo $device_info[0]['path']; ?>" width="100" />
					<h2>Device IP: <?php echo $device_info[0]['IP']; ?></h2>
					<h2>Device MAC: <?php echo $device_info[0]['MAC']; ?></h2>
					<h2>Last Seen: <?php echo $device_info[0]['LastSeen']; ?></h2>

					<h2>Device Make: <?php echo $device_info[0]['manf_name']; ?></h2>
					<h2>Device Model: <?php echo $device_info[0]['Model']; ?></h2>
					<h2>Device Type: <?php echo $device_info[0]['TypeName']; ?></h2>
				</div>
			</div>
			<div data-role="footer" data-theme="a">
				<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Close</a>
			</div>