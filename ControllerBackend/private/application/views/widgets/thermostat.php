			<div data-role="header" data-theme="a">
				<h1>Thermostat</h1>
			</div>
			<div role="main" class="ui-content">
				<div class="climate-control">
					<div>
						<i class="fa fa-thermometer-full fa-3x"></i>
						<h2 id="current-temp">21.5&#8451;</h2>
						<form class="full-width-slider">
							<label for="slider-12" class="ui-hidden-accessible">Slider:</label>
							<input name="slider-12" class="slider" id="temp-slider" min="16" max="34" value="18" type="range">
						</form>
						<p>Set Temp</p>
						<h2 id="set-temp" data-temp="22">22&#8451;</h2>
					</div>
				</div>
			</div>
			<div data-role="footer" data-theme="a">
				<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Cancel</a>
				<a href="#" id="save-temperature" data-id="0" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b">Save</a>
			</div>