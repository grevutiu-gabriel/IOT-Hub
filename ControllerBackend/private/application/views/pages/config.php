	<div data-role="page" id="<?php echo $title; ?>">
		<div data-role="header">
			<a href="/" data-icon="home" data-iconpos="notext" data-transition="fade">Home</a>
			<h1><img src="/assets/img/text-logo.png" width="100px"></h1>
			<a href="#" id="btn-logout" data-icon="lock" data-iconpos="notext" data-transition="fade">Logout</a>
		</div>
		<div data-role="main" class="ui-content">
        	<div class="logo-img">
        		<img src="/assets/img/logo-xsmall.png">
        		<h2 class="mc-text-center">Welcome <?php print_r ($_SESSION['Username']); ?>!</h2>
        	</div>
			<h3>Devices</h3>
			<p>Sorry - feature not yet available.</p>
			<form id="device-form">
				<label for="basic">Text Input:</label>
				<input type="text" name="name" id="basic" value="123"  />

				<label for="txtDeviceIP">Device IP</label>
				<input type="text" id="txtDeviceIP" title="If known - enter the IP address of device. Some models can scan by MAC address." />

				<a href="#" id="btn-login" class="ui-btn ui-btn-b ui-corner-all">Add Device</a>
			</form>
		</div>
		<div data-role="footer" data-position="fixed" data-fullscreen="true" data-tap-toggle="false">
			<div data-role="navbar">
				<ul>
					<li><a class="btn-menu" href="/devices" data-icon="grid">Devices</a></li>
					<li><a class="btn-menu" href="/rooms" data-icon="home">Rooms</a></li>
					<li><a class="btn-menu" href="/rules" data-icon="check">Rules</a></li>
					<li><a class="btn-menu" href="/users" data-icon="user">Users</a></li>
					<li><a class="btn-menu" href="/config" data-icon="gear">Config</a></li>
				</ul>
			</div>
		</div>
	</div>