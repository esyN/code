/*
### Copyright 2014 Dan Bean
###
### This program is free software: you can redistribute it and/or modify
### it under the terms of the GNU Lesser General Public License as published by
### the Free Software Foundation, either version 3 of the License, or
### (at your option) any later version.
###
### This program is distributed in the hope that it will be useful,
### but WITHOUT ANY WARRANTY; without even the implied warranty of
### MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
### GNU Lesser General Public License for more details.
###
### You should have received a copy of the GNU Lesser General Public License
### along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// JavaScript Document
//set up the connection - now done in getInteractions
//var service   = new intermine.Service({root: 'www.flymine.org/query'});

//functions for displaying the results
function handleError (error) {
    console.log("Something went wrong:", error);
	document.getElementById('int-results').innerHTML = ''
	document.getElementById('int-results').innerHTML += "The requested InterMine service appears to be down, please try again later.";
}
function handleRows (rows) {
    console.log("Found ", rows);
	var output = document.getElementById('int-results');
	//add and add all buttons should be disabled for network Merge-result
	var bit = window.currentNwName == 'Merge-result' ? 'disabled = "disabled"' : '';
	output.innerHTML = ''
	output.innerHTML += '<button class="btn btn-warning" id="add-all-btn" onclick="addAllFromInt()"'+bit+'>Add all</button>'
	
	var table = ""
	
	if(rows.length == 0){
		output.innerHTML = 'None found.'
	} else {
		output.innerHTML += '<div id="nfound"></div><br />' ; //this is the number after filtering duplicates
		table += '<table class="table table-hover">'
		table += '<thead><tr>';
		table += '<th>Matched Symbol</th>';
		table += '<th>Matched ID</th>';
		table += '<th>Interactor Symbol</th>';
		table += '<th>Interactor ID</th>';
		table += '<th>Interaction Type</th>';
		table += '<th>PubMed</th>';
		table += '<th>Add</th>';
		table += '</tr></thead><tbody>';

		

		count = 0;
		seenGenetic = []; //used to prevent duplicate interactions being retrieved
		seenPhysical = [];
		var pmids = {}; // interactor name -> [pmids]

		//first pass to combine pmids for edges with more than one citation
		pmidcol = rows[1].length-1;
		for(var i = 0; i<rows.length; i++){
			if(pmids.hasOwnProperty(rows[i][2])){
				//there is already an entry for this interactor so append the PMID if new
				if(pmids[rows[i][2]].indexOf(rows[i][pmidcol]) < 0){
					pmids[rows[i][2]].push(rows[i][pmidcol]);
				}
			} else {
				//create an entry for a new interactor
				pmids[rows[i][2]] = [rows[i][pmidcol]];
			}

		}
		
		for(var i = 0; i<rows.length; i++){
			//check whether we have already seen this intraction
			var duplicate = false;
			if(rows[i][4].indexOf('genetic') >= 0){ //indexof for compatibility with yeastmine
				if(seenGenetic.indexOf(rows[i][2]) >= 0){
					duplicate = true;
				} else {
					seenGenetic.push(rows[i][2]);
					count += 1;
				}
			} else if(rows[i][4].indexOf('physical') >= 0){
				if(seenPhysical.indexOf(rows[i][2]) >= 0){
					duplicate = true;
				} else {
					seenPhysical.push(rows[i][2]);
					count += 1;
				}
			} else {
				console.log('interaction type for ' + i + " not recognised")
			}
			if(duplicate == false){
				table += '<tr>'
				for (var j = 0; j < rows[i].length-1; j++) { //don't just print the final entry, need to make it a link
					table += '<td>' + rows[i][j] + '</td>';
				};

				//process pubmed links
				table += '<td>'
				for(var p = 0; p < pmids[rows[i][2]].length -1; p++){ //process all but the last one with a comma at the end
					table += '<a href="http://www.ncbi.nlm.nih.gov/pubmed/?term=' +pmids[rows[i][2]][p]+'" target="_blank">' +pmids[rows[i][2]][p]+ '</a>, ';

				}
				table += '<a href="http://www.ncbi.nlm.nih.gov/pubmed/?term=' +pmids[rows[i][2]][pmids[rows[i][2]].length-1]+'" target="_blank">' +pmids[rows[i][2]][pmids[rows[i][2]].length-1]+ '</a>';
				table += '</td>'

				//the parameter passed to the function should be the name that will appear on the node
				table += '<td><button class="btn btn-default" id="int-add-btn" onclick="addFromInt('+"'" + rows[i][2] +"', '" + rows[i][4] +"','" + pmids[rows[i][2]] +"'"  +')"'+bit+'>Add</button></td>'; //bit will disable button if needed
				table += '</tr>'
			}
		};
		table += '</tbody></table>';

		output.innerHTML += table;
		$('#nfound').text('Found ' + count + ' interactions.')
	};
}

function checkInteractorName(name){
	//check whether the name of an interactor would break the quoting in the HTML
	if(name.search("'") >=0 || name.search('\n') >=0 || name.search('"') >=0) {
		return false;
	} else {
		return true;
	}
};

//yeastmine interactions query
// var query = {"model":{"name":"genomic"},"select":["Interaction.gene1.secondaryIdentifier","Interaction.gene1.symbol","Interaction.gene2.secondaryIdentifier","Interaction.gene2.symbol","Interaction.details.type"],"where":[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":"sod1","extraValue":"S. cerevisiae"}]};

//function to execute the query
function getInteractions(){
	document.getElementById('int-results').innerHTML = 'Retrieving... <br />'
	loading();
	
	//get the gene name, organism and interaction type
	var sym = cy.$(':selected').data('name');
	//instead of this, just add nodes near whatever is currently selected
	//window.intermineSource = cy.$(':selected'); //used to determine where to add the retrieved nodes and edges
	var organism = $('#organisms').val()
	var intType = $('#interaction-type').val()

	//basic query
	var query = {model:{"name":"genomic"},select:["Interaction.gene1.symbol","Interaction.gene1.secondaryIdentifier","Interaction.gene2.symbol","Interaction.gene2.secondaryIdentifier","Interaction.details.type","Interaction.details.experiment.publication.pubMedId"],orderBy:[{"Interaction.gene2.secondaryIdentifier":"ASC"}],where:[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":sym,"extraValue":organism}]};
//  var query = {"model":{"name":"genomic"},"select":["Interaction.gene1.secondaryIdentifier","Interaction.gene1.symbol","Interaction.gene2.secondaryIdentifier","Interaction.gene2.symbol","Interaction.details.type"],"constraintLogic":"A and B","where":[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":"sod1","extraValue":"S. cerevisiae"},{"path":"Interaction.details.type","op":"=","code":"B","value":"genetic interactions"}]};
	//set up the service connection - as more options are implemented, we may want to make getService to set it appropriately.
	//is there an option to use a more generic service?

	if(organism == "H. sapiens"){
		var service   = new intermine.Service({root: 'http://www.metabolicmine.org/beta/'});
	} else if (organism == 'S. cerevisiae') { 
		var service   = new intermine.Service({root: 'http://yeastmine.yeastgenome.org/yeastmine/' });
		//query.orderBy = [{"Gene.interactions.gene2.symbol":"ASC"}]
	} else {
		var service   = new intermine.Service({root: 'www.flymine.org/query'});
	};
	
	
	
	//if an interaction type was specified, add that to the query. Using value: '' seems to return nothing rather than both
	if(intType != 'any'){
		if(organism == "S. cerevisiae"){
			intType += " interactions"	//needed for yeastmine
			query['constraintLogic'] = "A and B"
		}
		query.where.push({path:"Interaction.details.type","op":"=","code":"B","value":intType})
	};
	
	//.values only lets you get one column!
	//var querying = service.values(query);
 // var query = {"model":{"name":"genomic"},"select":["Interaction.gene1.secondaryIdentifier","Interaction.gene1.symbol","Interaction.gene2.secondaryIdentifier","Interaction.gene2.symbol","Interaction.details.type"],"where":[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":"sod1","extraValue":"S. cerevisiae"}]};


	var querying = service.rows(query);
	querying.then(handleRows, handleError);

	doneLoading();
};

//function to add a progress bar while loading
function loading(){
	var bar = '<div id="loading" class="progress progress-striped active">' +
  '<div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">' +
    '<span class="sr-only">45% Complete</span></div></div>'


	document.getElementById('loading-container').innerHTML += bar;
};

function doneLoading(){
	$('#loading').remove();
};


//add found nodes to the network
//will need to check that the name is unique in the network - for disperse places
function addFromInt(name, type,pmids){
	//pubmed ids (pmids) must be a comma separated string without spaces - i.e. the result of [].toString()
	//pmid ignored for petri nets as they can only be automatically associated with edges. For petri nets they are transition properties created manually
	console.log('trying to add a node from query results: ' + name);

	//check the name is ok - e.g. YIL154C is IMP2' <- with a ' which breaks the quoting
	if(name.search("'") >=0 || name.search('\n') >=0 || name.search('"') >=0) {
		alert('this name contains punction that prevents it being automatically added: ' + name);
	} else {

		//add the node
		var selectedPos = cy.$(':selected').position();
		var xPos = selectedPos.x + randomFromInterval(-150,150);
		var yPos = selectedPos.y + randomFromInterval(-150,150);
		var default_id = "n" + window.stack.metadata['nodecounter']; //used below to find this node again if it is disperse
		var default_name = default_id;
		var makeNode = true; //whether we need to make a new node
		if(window.esynOpts.type == 'Graph' && cy.filter("node[name = '" + name +"']").length ==1 ){
			//if we're adding to a binary network and there is exactly one exsiting reference to that node in the current
			//network, we create an edge to that node rather than creating an appearance of a disperse place
			makeNode = false;
		}
		
		//if there is already a place with this name we will create the node then sort out disperse place details
		if(makeNode){
			var default_marking = 0;
			cy.add([{
						group : "nodes",
						data : {
							id : default_id,
							name : default_name, //create with a dummy name then change - this way we can use make_disperse
							marking : default_marking //always initialise as 0, transitions will have multiplicity = 0, doesn't matter
							
						},
						classes: 'place',//window.ntype,
						position : { //in this case we must use position not renderedposition
							x : xPos, // x position,
							y : yPos // y position
						},
			}]);
			//update counter
			window.stack.metadata['nodecounter'] += 1;
			//update node/place name metadata
			window.stack.metadata[window.ntype][default_name] = default_marking;
			//check if we're making a disperse place - the node has to already exist as a normal place for this to work
			var newNode = cy.filter("node[id = '" + default_id +"']");
			if(name in window.stack.metadata.place){
					make_disperse(newNode,name); 
			} else {
				update_place(newNode, name, default_marking);
			};
				
			console.log('node created')
		}
		
		/* 
			will need a global reference to the originating node?
			or maybe I could find a way to pass it's ID in from the button when they're generated

			source and target may need to be the other way around for directed edges
		*/
		//add the edge to the currently selected node if we are making a binary network
		
		if(window.esynOpts.type == 'Graph'){
			var setID = window.stack.metadata.edgecounter.toString();
			selectedId = cy.$(':selected').id();
			//if we didn't create a new node, get the ID of the existing node to create an edge from the selected node
			if(makeNode == false){
				default_id = cy.filter("node[name = '" + name +"']").id()
			}
			cy.add([{
						group: "edges",
						data: {
							id: "e" + setID,
							source: selectedId,
							target: default_id, //when the interacting node was created above we set its ID to default_id
							multiplicity: 1 //initialise as 1. Both normal and inhibitor edges can have a multiplicity greater than 1 
						},
						//classes are a space separated list of classes as a string
						//currently always set class to "normal" rather than "directed". If set to window.etype, it will be whatever type of edge the user is set to
						classes: 'normal ' + type.split(' ')[0] //type.split as some databases return 'genetic interactions' and we need to remove ' interactions'
					}])  
			window.stack.metadata.edgecounter += 1;
			//add citations
			window.stack.metadata.citations['e'+setID] = pmids.split(',')
			console.log('edge created')
		}
	};
};

//get a random number from a given interval. Used to place new nodes around the currently selected node
function randomFromInterval(from,to)
{
	return Math.floor(Math.random()*(to-from+1)+from);
}
  

//function to add all interactions
function addAllFromInt(){
	loading();

	//for some gene names it might not be possible to add automatically
	//get all the buttons in the interactions table
	$('*[id*=int-add-btn]').each(function(){
		try{
			$(this).click();
		}
		catch(err){
			alert('A node could not be added automatically. Error: ' + err)
		}

	})
	
	//disable the add all button
	$('#add-all-btn').prop('disabled',true);

	doneLoading();
}

/*
yeastmine interactions more detail
var query = {"model":{"name":"genomic"},"select":["Gene.primaryIdentifier","Gene.symbol","Gene.secondaryIdentifier","Gene.sgdAlias","Gene.name","Gene.organism.shortName","Gene.interactions.details.annotationType","Gene.interactions.details.phenotype","Gene.interactions.details.role1","Gene.interactions.details.experimentType","Gene.interactions.gene2.symbol","Gene.interactions.gene2.secondaryIdentifier","Gene.interactions.details.experiment.name","Gene.interactions.details.relationshipType.name"],"constraintLogic":"A and B","orderBy":[{"Gene.primaryIdentifier":"ASC"}],"where":[{"path":"Gene.organism.shortName","op":"=","code":"B","value":"S. cerevisiae"},{"path":"Gene","op":"LOOKUP","code":"A","value":"act1"}]};
*/

function yeastTest(){
	document.getElementById('int-results').innerHTML = 'Retrieving... <br />'
	
	//get the gene name, organism and interaction type
	var sym = cy.$(':selected').data('name');
	//instead of this, just add nodes near whatever is currently selected
	//window.intermineSource = cy.$(':selected'); //used to determine where to add the retrieved nodes and edges
	var organism = $('#organisms').val()
	var intType = $('#interaction-type').val()
	
	//set up a basic query - gets modified below depending on organism and interaction type
	//var query = {model:{"name":"genomic"},select:["Gene.interactions.details.allInteractors.symbol"],orderBy:[{"Gene.interactions.details.allInteractors.symbol":"ASC"}],where:[{"path":"Gene","op":"LOOKUP","code":"A","value":sym,"extraValue":organism}]};
	//var query = {model:{"name":"genomic"},select:["Gene.primaryIdentifier","Gene.symbol","Gene.secondaryIdentifier","Gene.sgdAlias","Gene.name","Gene.organism.shortName","Gene.interactions.details.annotationType","Gene.interactions.details.phenotype","Gene.interactions.details.role1","Gene.interactions.details.experimentType","Gene.interactions.gene2.symbol","Gene.interactions.gene2.secondaryIdentifier","Gene.interactions.details.experiment.name","Gene.interactions.details.relationshipType.name"],constraintLogic:"A and B",orderBy:[{"Gene.primaryIdentifier":"ASC"}],where:[{"path":"Gene.organism.shortName","op":"=","code":"B","value":"S. cerevisiae"},{"path":"Gene","op":"LOOKUP","code":"A","value":"act1"}]};
	//var query = {model:{"name":"genomic"},select:["Gene.Interaction*",],constraintLogic:"A and B",orderBy:[{"Gene.primaryIdentifier":"ASC"}],where:[{"path":"Gene.organism.shortName","op":"=","code":"B","value":"S. cerevisiae"},{"path":"Gene","op":"LOOKUP","code":"A","value":"act1"}]};

	//flymine
	var query = {model:{"name":"genomic"},select:["Interaction.gene1.symbol","Interaction.details.allInteractors.symbol","Interaction.details.type"],constraintLogic:"A and B",orderBy:[{"Interaction.gene1.symbol":"ASC"}],joins:["Interaction.details"],where:[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":"pros","extraValue":"D. melanogaster"},{"path":"Interaction.details.type","op":"=","code":"B","value":"genetic"}]};

	var query = {model:{"name":"genomic"},select:["Interaction.gene1.symbol"],constraintLogic:"A and B",orderBy:[{"Interaction.gene1.symbol":"ASC"}],joins:["Interaction.details"],where:[{"path":"Interaction.gene1","op":"LOOKUP","code":"A","value":"pros","extraValue":"D. melanogaster"},{"path":"Interaction.details.type","op":"=","code":"B","value":"genetic"}]};
	//set up the service connection - as more options are implemented, we may want to make getService to set it appropriately.
	//is there an option to use a more generic service?
	//var service   = new intermine.Service({root: 'http://yeastmine.yeastgenome.org/yeastmine/' });
	
	//flymine
	var service = new intermine.Service({
        root: "http://www.flymine.org/release-38.0/"
    });
	var querying = service.values(query);

	querying.then(handleRowsTest, handleError);
};

function handleRowsTest (rows) {
    console.log("Found ", rows);
	var output = document.getElementById('int-results');
	output.innerHTML = ''
	for (var i = 0; i < rows.length; i++) {
		console.log(rows[i]);
	};
	/*
	output.innerHTML += '<button class="btn btn-warning" id="add-all-btn" onclick="addAllFromInt()">Add all</button>'
	
	var table = ""
	
	if(rows.length == 0){
		output.innerHTML = 'None found.'
	} else {
		output.innerHTML += 'Found '+ rows.length + ' interactions.<br />' ;
		table += '<table class="table table-hover">'
		table += '<thead><tr>';
		table += '<th>Node</th>';
		table += '<th>Add</th>';
		table += '</tr></thead><tbody>';
		
		for(var i = 0; i<rows.length; i++){
			table += '<tr>'
			table += '<td>' + rows[i] + '</td>';
			//the parameter passed to the function should be the name that will appear on the node
			table += '<td><button class="btn btn-default" id="int-add-btn" onclick="addFromInt('+"'" + rows[i] +"'"  +')">Add</button></td>';
			table += '</tr>'
		};
		table += '</tbody></table>';

		output.innerHTML += table;
	};
	*/
}

