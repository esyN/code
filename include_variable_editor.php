<h4>Edit model variables</h4>

<button onclick="ve_refresh()" class="btn btn-info">Refresh</button>


<div> <!-- editing variable groups -->

<div class="panel panel-default">
	<div class="panel-heading">
    <h3 class="panel-title">Create and edit variable groups</h3>
  </div>
  <div class="panel-body">
<div>Create a new variable group. Name: <input type="text" id='ve-new-group-name'><button onclick="ve_create_new_group()" class="btn btn-success">Create</button></div>
<div>
	Select a group to edit <select id='ve-var-group-edit' onchange="ve_display_group()"></select>

	<h4 id="ve-grp-selected">Select a group to edit</h4>
	<p>Variables in group:</p>
	<select id="ve-grp-contains" name="ve-group-contains" class="input-sm" multiple="multiple"></select>
	<br>
	Remove selected variables from this group <button class="btn btn-danger" onclick="ve_remove_from_group()">Remove</button>
	<br>
	Rename group to <input type="text" id='ve-group-rename'><button class="btn btn-info" onclick="ve_rename_group()">Rename</button>
	<br>
	Delete group <button class="btn btn-danger" onclick="ve_delete_group()">Delete</button>

</div>

  </div>
</div>



</div>  <!--end editing variable groups -->


<div> <!-- editing variables-->

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Edit variables</h3>
  </div>
  <div class="panel-body">
<div><p>Select a variable to edit its properties and see how it is used in the model</p>
<select id="ve-select-var" onchange="ve_display_variable()">
	<option selected="selected" value="">Select a variable</option>
</select>
</div>


<div id="ve-var-info-container">
	<h4 id="ve-selected">Select a variable to edit</h4>
	<div><input type="checkbox" id="ve-required" onchange="ve_set_required()"> Required variable<br> </div>
	<div>	Rename to <input type="text" id='ve-var-rename'><button class="btn btn-info" onclick="ve_rename_variable()">Rename</button>
 </div>
	<div>
		<label for="ve_var_description"	>Details (optional)</label>
		<textarea class="form-control" rows="3" id="ve_var_description"></textarea>
		<button type="button" class="btn btn-success" onclick="ve_set_description()">Save description</button>
	</div>
	<div id='ve-current-group'>Current group:</div>
	<div>Assign to group <select id='ve-var-group'></select>
		<button class="btn btn-success" onclick="ve_set_group()">Assign</button>
	</div>
	<table id='ve-var-info' class="table"></table>
</div>
 </div>
</div>



</div> <!-- end editing variables-->
