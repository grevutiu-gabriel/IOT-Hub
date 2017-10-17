		<div data-role="main" class="ui-content">
        	<div class="logo-img">
        		<img src="/assets/img/logo-xsmall.png">
        		<h2 class="mc-text-center">Welcome <?php print_r ($_SESSION['Username']); ?>!</h2>
        	</div>
			<div class="climate-control">
				<div>
					<i class="fa fa-thermometer-full fa-3x"></i>
					<h2>21.5&#8451;</h2>
					<form class="full-width-slider">
						<label for="slider-12" class="ui-hidden-accessible">Slider:</label>
						<input name="slider-12" class="slider" id="temp-slider" min="16" max="34" value="18" type="range">
					</form>
					<p>Set Temp</p>
					<h2 id="set-temp">22&#8451;</h2>
				</div>
			</div>
		</div>