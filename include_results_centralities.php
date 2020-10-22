<div class="row3">
	
<h4>Find the Most Central Nodes in the Network <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#centralityinfoModal">[?]</button></sup></a></h4>

	<div class="alert alert-warning alert-dismissible" role="alert">
  	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  	<strong>Warning:</strong> If the network is changed after the calculation, the results shown might not be correct.
	</div>	

  <b>Advanced Options: </b> <input type="checkbox" id="AdvOpt" name="AdvOpt" data-toggle="toggle">

	<div id="gen_selAdvOpt" style="display:none;" >
	<div id="chooseGraphAttributes">
	

	<?php
	  if($getNetworkType == 'Graph'){	
		echo '<table style="width:100%">
			  <tr>
				<td><center><h4> <input type="radio" id="directed" name="chooseGraphAttributes" onclick="" > Directed Edges  	</h4></center></td>
				<td><center><h4> <input type="radio" id="undirected" name="chooseGraphAttributes" checked="checked" onclick="" > Undirected Edges </h4></center></td>		
				<td><center><h4> <input type="radio" id="mixed" name="chooseGraphAttributes" onclick="" > Mixed Edges (slow) </h4></center></td>
			  </tr>
			</table>';
	  }else{
		echo ' <table style="width:100%">
			  <tr>
				<td><center><h4> <input type="radio" id="directed" name="chooseGraphAttributes" checked="checked" onclick="" > Directed Edges  	</h4></center></td>
				<td><center><h4> <input type="radio" id="undirected" name="chooseGraphAttributes" onclick="" > Undirected Edges </h4></center></td>		
				<td><center><h4> <input type="radio" id="mixed" name="chooseGraphAttributes" onclick="" > Mixed Edges (slow) </h4></center></td>
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
		Centrality:
		<table style="width:100%">
		<tr>
			<td><input type="radio" id="centralityDegree" name="upfiletype" onclick="" > Degree <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#degreeinfoModal">[?]</button></sup></a></td>
			<td><input type="radio" id="centralityCloseness" name="upfiletype" onclick="" > Closeness <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#closenessinfoModal">[?]</button></sup></a></td>
			<td><input type="radio" id="centralityBetweenness" name="upfiletype" checked="checked" onclick="" > Betweenness <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#betweennessinfoModal">[?]</button></sup></a></td>
			<td><input type="radio" id="centralityEccentricity" name="upfiletype" onclick="" > Eccentricity <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#eccentricityinfoModal">[?]</button></sup></a></td>
		</tr>
		<tr>
			<td><input type="radio" id="centralityRadiality" name="upfiletype" onclick="" > Radiality <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#radialityinfoModal">[?]</button></sup></a></td>
			<td><input type="radio" id="centralityStress" name="upfiletype" onclick="" > Stress <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#stressinfoModal">[?]</button></sup></a></td>		
			<td><input type="radio" id="centralityCentroidValue" name="upfiletype" onclick="" > Centroid Value <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#centroidinfoModal">[?]</button></sup></a></td>		
			<td><input type="radio" id="centralityCI" name="upfiletype" onclick="" > Collective Influence, Radius = <input type="text" id="attackRadiusCI" size="2" value="2"><a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#collInfluenceinfoModal">[?]</button></sup></a></td>			
		</tr>
		</table>
	</div>
	
	

<div id="networkDisruption">
	<br>

	<b>Disrupt the Network:</b> <a><sup><button type="button" class="btn btn-link btn-sm btn-tight" data-toggle="modal" data-target="#disruptioninfoModal">[?]</button></sup></a>

	<!--<input type="checkbox" id="AdvOpt3" name="AdvOpt3" data-toggle="toggle" onclick="menuGenes()"> -->
	<input type="checkbox" id="AdvOpt3" name="AdvOpt3" data-toggle="toggle" onclick="menuGenes();$('html, body').animate({ scrollTop: $(document).height() }, 'slow');">
	
	<div id="gen_selAdvOpt3" style="display:none;" >
	<br />

		<b>Select Genes to exclude in the Calculation:</b><br/>
	
		<div style="height:220px;width:100%;border:1px solid #ccc;overflow:auto;">
			<div id="geneListDisrupt"></div>
		</div>
	
		
	</div> <!--hide display none-->
</div>
</div> <!-- hide display none -->

<div id="optionsButtons">
		<p></p>
		<button type="button"  id="execute" class="btn btn-success" onclick="selectCalculation1()"> Calculate </button>		
		<div id="buttonToChange" style="display: inline"></div>
		<div id="buttonToChange2" style="display: inline">
			<button type="button" id="nwParam" class="btn btn-default" data-toggle="modal" data-target="#printNwParameter" onclick="getNetworkParameter()">Network Parameters</button> 
		</div>
		<div id="buttonToChange3" style="display: inline"></div>
		
		<button type="button" id="exit" class="btn btn-danger" onclick="resetAllSettings()">Reset Style</button> 
	</div>


<!--	
	<div id="optionsButtons">
		<p></p>
		<button type="button"  id="execute" class="btn btn-success" onclick="selectCalculation1()"> Calculate </button>		
		<div id="buttonToChange" style="display: inline">
			<button type="button" id="print-btn" class="btn btn-default" data-toggle="modal" data-target="#printCentralities">View Results</button>
		</div>
		<button type="button" id="nwParam" class="btn btn-default" data-toggle="modal" data-target="#printNwParameter" onclick="getNetworkParameter()">Network Parameters</button>
		<button type="button" id="print-btn" class="btn btn-info" href="#top" onclick="selectResize();$('html,body').animate({scrollTop:0},'slow');return false;" >Visualise Parameters</button> 
		
		<button type="button" id="exit" class="btn btn-danger" onclick="resetAllSettings()">Reset Style</button> 
		
	</div>    -->

	<br>
	
</div>