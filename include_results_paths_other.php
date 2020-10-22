<div class="row4">

<h4>Select Two Nodes and Find the Shortest Path Between Them <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#pathinfoModal">[?]</button></sup></a></h4>

<div class="alert alert-warning alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong>Warning:</strong> If the network is changed after the calculation, the results shown might not be correct.
</div>

Advanced Options:  <input type="checkbox" id="AdvOpt2" name="AdvOpt2" data-toggle="toggle">

	<div id="gen_selAdvOpt2" style="display:none;" >

<div id="chooseGraphAttributes">
	<?php
	  if($getNetworkType == 'Graph'){
		echo '<table style="width:100%">
			  <tr>
				<td><center><h4> <input type="radio" id="directed2" name="chooseGraphAttributes2" onclick="" > Directed Edges  	</h4></center></td>
				<td><center><h4> <input type="radio" id="undirected2" name="chooseGraphAttributes2" checked="checked" onclick="" > Undirected Edges </h4></center></td>
				<td><center><h4> <input type="radio" id="mixed2" name="chooseGraphAttributes2" onclick="" > Mixed Edges (slow) </h4></center></td>
			  </tr>
			</table>';
	  }else{
		echo ' <table style="width:100%">
			  <tr>
				<td><center><h4> <input type="radio" id="directed2" name="chooseGraphAttributes2" checked="checked" onclick="" > Directed Edges  	</h4></center></td>
				<td><center><h4> <input type="radio" id="undirected2" name="chooseGraphAttributes2" onclick="" > Undirected Edges </h4></center></td>
				<td><center><h4> <input type="radio" id="mixed2" name="chooseGraphAttributes2" onclick="" > Mixed Edges (slow) </h4></center></td>
			  </tr>
			</table>';
	  }
	?>
	</div>


	<div id="networkEdges">
		<div class="col-md-4 col-sm-4 col-xs-4">
			<center>
				<img src="directedGraph.png" alt="directedGraph" style="width:100px;height:80px;">
			</center>
		</div>
	<div class="col-md-4 col-sm-4 col-xs-4">
		<center>
			<img src="undirectedGraph.png" alt="undirectedGraph" style="width:100px;height:80px;">
		</center>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-4">
		<center>
			<img src="mixedGraph.png" alt="mixedGraph" style="width:100px;height:80px;">
		</center>
	</div>
   </div>


	<div id="chooseCalculation">
		<p></p>
<br />
		<table style="width:100%">
		<tr>
			<td><input type="radio" id="pathwayUndirected" name="upfiletype2"  onclick="" > Find Shortest Path (source <sub><font size="5">&harr;</font></sub> target) </td>
			<td> <input type="radio" id="pathwayDirected" name="upfiletype2" checked="checked" onclick="" > Find Shortest Path (source <sub><font size="5">&rarr;</font></sub> target) </td>
		</tr>

		</table>
	</div>



	</div> <!-- hide gen_selAdvOpt2 display none -->

	<div id="optionsButtons">
		<p></p>
		<button type="button"  id="execute" class="btn btn-success" onclick="selectCalculation2();$('html,body').animate({scrollTop:0},'slow');return false;"> Select &amp; Calculate </button>
		<button type="button" id="print-btn" class="btn btn-default" data-toggle="modal" data-target="#printPath">View Shortest Path Details</button>
		<button type="button" id="nwParam" class="btn btn-default" data-toggle="modal" data-target="#printNwParameter" onclick="getNetworkParameter()">Network Parameters</button>
		<button type="button" id="exit" class="btn btn-danger" onclick="resetAllSettings()">Reset Style</button>

	</div>

	<br>

</div>
