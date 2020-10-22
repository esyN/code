<!-- ip-* info pannel ids -->
<!-- BS3 bug - data-parent only works if child elements are in a .panel -->
<div class="panel">
<input type="hidden" id='ip-current-element' name='ip-current-element'>

<div class="collapse in" data-parent="#info" id="ip-welcome-msg">
	<p>Welcome to esyN!</p>
	<p>Click in the blue area to create/ edit nodes and edges. </p>

	<p>Add conditions to edges to create the logic of your model.</p>
	<p>In the Advanced Tools you can create custom rules, edit variables and run your model.</p>
</div>

<div class="collapse" data-parent="#info" id="ip-edge-started">
	Creating <span id="ip-started-edge-type"></span> edge from: <span id="ip-started-edge-name"></span>.
	<!-- //create a button that will allow the user to cancel edge creation -->
	<button type="button" id="cancel" class="btn btn-danger" onclick="cancel_edge()">Cancel edge creation</button>
</div>

<div class="collapse" data-parent="#info" id="ip-node-info">
	<label for="set_node_name">Name:</label><input type="text" name="set_node_name" id="set_node_name">
	<button type="button" id="set" class="btn btn-success" onclick="ip_set()">Set</button>
	<p>Select:</p>
	<!-- <button type="button" class="btn btn-primary" id="select_in" onclick="select_incomers('n0')">Incoming</button> -->
	<button type="button" class="btn btn-primary" id="select_in" onclick="ip_select_incomers()">Incoming</button>
	<button type="button" class="btn btn-primary" id="select_out" onclick="ip_select_outgoers()">Outgoing</button>
	<button type="button" class="btn btn-primary" id="select_neighbors" onclick="ip_select_neighborhood()">Connected</button>
	<p>Visualise:</p>
	<button type="button" class="btn btn-primary" onclick="show_paths_from_root()">Paths to node</button>
	<button type="button" class="btn btn-primary" id="highlight_node" onclick="ip_toggle_highlight()">Toggle highlight</button>
	<p>Remove:</p>
	<button type="button" id="remove" class="btn btn-danger" onclick="remove_element()">Delete selected</button>

</div>

<div class="collapse" data-parent="#info" id="ip-edge-info">
	<div>
		Edit edge conditions
		<br/>
			<label class="control-label" for="textconditionprop">Property:</label>
			<input type="text" class="form-control typeahead" id="textconditionprop">
			<label for="conditionOperatorSelect">Operator:</label>
			<select id="conditionOperatorSelect">
<!-- options created based on edge -->
			</select>

			<label class="control-label" for="textconditionval">Value:</label>
			<input type="text" class="form-control" id="textconditionvalue">

			<button class="btn btn-success" type="button" id="buttonaddcondition" onclick="ip_addConditionButton()">Add Condition</button>

	</div>
	<br/>
	<div class="control-group">
		<div class="controls">
			<select id="selectcondition" name="selectcondition" class="input-sm wide" multiple="multiple">
			</select>
		</div>
	</div>

	<div class="control-group">
		<button id="buttonremovecondition" class="btn btn-danger" onclick="ip_removeConditionButton()">Remove Condition(s)</button>
	</div>

	<button type="button" id="remove" class="btn btn-danger" onclick="remove_element()">Delete selected</button>

</div>


</div> <!-- end of container panel. Any .collapse must be within this. -->
