<h4>Edit calculators and rules</h4>

<p>Calculators infer missing values based on user input. Rules determine when the model is valid.</p>

<p>You can refer to variables in your model by including the name in single quotes e.g.
<pre>IF: 'var_a' > 5 and ('x' < 10 or 'y' < 15)</pre>
</p>


<button onclick="re_refresh()" class="btn btn-info">Refresh</button>
<br>
<div>
	<div>
		Rule name: <input type="text" name="re-rule-name" id="re-rule-name" size="100" placeholder="Text">
	</div>
<div>
	IF: <input type="text" name="re-rule-string" id="re-rule-string" size="100" placeholder="Formula">
</div>
<div>
	THEN:
	<select id="re-then" onchange="re_select_then()">
		<option value="set-val">Set value of</option>
		<option value="not-valid">Input not valid</option>
	</select>
	<div id="re-set-val-of-container">
	select a variable<select id='re-set-val-of'></select>
	set value to <input type="text" name="re-rule-value" id="re-rule-value" placeholder="Formula">
	</div>
</div>
<br>
<button onclick="re_create_rule()" class="btn btn-success">Create</button>
</div>
<br>
<div id="re-rule-list-container">
	<table id='re-active-rule-list' class="table"></table>
</div>
