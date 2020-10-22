<h4>Interactive App</h4>
<p>You will only be asked for required information to move through the tree at each step. Note that if there is sufficient information to follow a path, it will be taken even if other potential options cannot be fully evaluated.</p>

<button onclick="build_app(false, true)" class="btn btn-success">Start</button>

<button onclick="run_dt_model('interactive', false, false)" class="btn btn-primary">Next</button>
<button onclick="clear_dt_results()" class="btn btn-warning">Reset</button>
<button onclick="start_tracking_mode()" class="btn btn-warning">Create dataset</button>

<div id='tracking_tools' style="display: none;">
	<br />
	<div class="panel panel-default">
		<div class="panel-heading">
	    <h3 class="panel-title">Dataset manager</h3>
	  </div>
	  <div class="panel-body">
			<p>Note: datasets are not part of your model and are not saved to esyN. Results must be downloaded locally when finished.</p>
	  	<button onclick="tracking_next('previous')" >
			Previous row
		</button>
		<button onclick="tracking_next('next')">
			 Next row
		</button>
		<!-- <button>View all</button> -->
		<button onclick="tracking_delete_current()">Clear row</button>
		<button onclick="tracking_reset()">Clear dataset</button>

		<button onclick="tracking_download()">
			 Download
		</button>
		<div id="tracking_internal_id">Current row:</div>
		<div id="tracking_total_rows">Total rows:</div>
		<p>Manual row identifer: <input type="text" id="tracking_manual_id" name="tracking_manual_id"></p>

	  </div>
	</div>
</div>


<!-- BS3 bug - data-parent only works if child elements are in a .panel -->
<button class="btn btn-link" data-toggle="collapse" data-target="#log-collapse-container" aria-expanded="false" aria-controls="dt-model-log">Show log</button>
<div class="panel">
	<div class="collapse" data-parent="#adv_a" id='log-collapse-container'>
		<br/>
		<div id="dt-model-result">Results will be shown here</div>
		<div id="dt-model-log"></div>
	</div>
</div> <!-- end of container panel. Any .collapse must be within this. -->


<div id="dt-model-container">
	<table id='dt-model-params' class="table model-input-table"></table>
</div>
