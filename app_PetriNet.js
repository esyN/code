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

/*
interactive app for construction of petrinets
@author dmb57
*/

//set up global variables
window.nmode = true;
window.sourcenode = -1; //will track the id of the source node in edge creation
window.sourcetype = -1; //will track the type of the course node in edge creation
window.ntype = "place" //type of node to be added
window.etype = "normal" //type of edge to be added
window.nodecounter = 0; //to keep track of the number of nodes that have been created
window.networkType = 'PetriNet' //can be petri or binary - affects the behaviour of adding nodes from intermine data in getInteractions.js

//window.currentNwName is set up by cytoscape onload
window.workingID = "none"; //the ID to use for the current stack when saving. If "none" then we are starting a new stack and will be given an ID to use

//network data stack
window.stack = {}
window.stack['metadata'] = {} //to keep track of stack-level properties e.g. number of nodes
window.stack.metadata['nodecounter'] = 0;
window.stack.metadata['edgecounter'] = 0; //keep track of the number of edges that have been created - otherwise if you delete edges you can't add new ones any more
//call the place and transition trackers the same as window.ntype so that can be used to access the correct data
window.stack.metadata['place'] = {}; //object for all stack place names to keep track of adding disperse places
window.stack.metadata['transition'] = {}; //object for all transition names to prevent duplicates - could use a list? this way they're accessed the same as pnames
//trackers for place classes. Entries only exist of they are not default (normal place with no classes)
window.stack.metadata['contains'] = {} //what places are a sub class of each coarse place
window.stack.metadata['isa'] = {} //what coarse places each node is a sub class of
//keep track of disperse places
window.stack.metadata['disperse'] = {} //keyed by disperse place NAME -> network: [id(s)]
//keep track of parameter k
window.stack.metadata['k'] = {} //keyed by place NAME -> k (if a name is not a key for this network we know k is 1)
//citations for elements
window.stack.metadata.citations = {}; //keyed by element id -> [pmid]

//if we have a project id, the user cannot change the label from the editor
if(window.esynOpts.projectid != '-1' || window.esynOpts.publishedid != -1){
	//remove the ability to upload as this will wipe all the current data in the project
	var uploadbtn = $('#upload-btn');
	uploadbtn.prop('disabled',true);
	uploadbtn.text("Can't upload into an existing project");
	
} else if(window.esynOpts.projectid == '-1' && window.esynOpts.publishedid == '-1') {
	//if you are able to set the filename, it will generate a default one based on the time and date
	//this name will not be used if you're viewing a project and save a copy - then it will generate one in save_to_server()
	$('#set_filename').prop('value',getDefaultFilename())
};

//if we're working with a published project, disable to save button
//even if somebody re-enabled it, the save function would create a new project id
if(window.esynOpts.publishedid != '-1'){
	$('#save-online').prop('disabled',true);

	//fill in the name
	getPublishedName(window.esynOpts.publishedid, setNameForm);
	$('#set_filename').prop('disabled',true)
};
if(window.esynOpts.projectid != '-1'){
	//fill in the name
	getProjectName(window.esynOpts.projectid, setNameForm);
	$('#set_filename').prop('disabled',true)
};

//if the user is not logged in, disable the "save" and "save a copy" buttons and change their text
if(window.esynOpts.loggedin == false){
	var savebtn = $('#save-online');
	savebtn.prop('disabled',true);
	savebtn.text('Log in to save online');
	var savecopybtn = $('#save-copy');
	savecopybtn.prop('disabled',true);
	savecopybtn.text('Log in to save a copy online');
}

//stack of graphs
//stack['name'] = network_JSON, maybe stringified
//if the name of a network is changed, 
//	delete stack['name'] 
//	stack['newname'] = JSON


$('#cy').cytoscape({
showOverlay: false, //turn off the cytoscape.js text in the bottom right
boxSelectionEnabled: false, //turn off click and drag

  layout: {
    name: 'grid',
    //ready: loadNet(TOPLEVEL)
  },
	maxZoom: 4,
	minZoom: 0.1,
  
  style: cytoscape.stylesheet()
    .selector('node')
      .css({
      	'color': '#000000',
        'content': 'data(name)',
        'text-valign': 'center',
        'background-color': '#FFFFFF',
        'border-width': 1,
        'border-color': '#707070'
      })
	.selector('node.disperse')
      .css({
        'border-width': 2,
        'border-color': '#2980b9'
      })
	.selector('node.contains')
      .css({
		  'background-color':'#e67e22'
      })
	  .selector('node.coarsetransition')
      .css({
		  'border-width': 2,
		  'border-color':'#27ae60'
      })
    .selector('node:selected')
      .css({
        'border-width': 3,
		'background-color': '#f1c40f'
      })
    .selector('edge')
      .css({ 
         content: 'data(multiplicity)', // maps node label to data.label
        'line-color': '#000', 
		'source-arrow-color': '#000',
        'target-arrow-color': '#000' 
      }) 
	.selector('edge:selected')
      .css({ 
	  'width':3,
        'line-color': '#f1c40f', 
		'source-arrow-color': '#f1c40f',
        'target-arrow-color': '#f1c40f' 
      }) 
    .selector('edge.normal')
      .css({
        'target-arrow-shape': 'triangle',
      })
    .selector('edge.inhibitor')
    	.css({
    		'target-arrow-shape' : 'circle'
    	})

    .selector('.faded')
      .css({
        'opacity': 0.25,
        'text-opacity': 0.25
    })
    .selector('node.transition')
    	.css({
    	'shape': 'square'
    	
      }),
  
  ready: function(){
  	console.log('petrinet builder app loaded')
    var cy = this;
	//if we have been given a project id, load that project
	if(window.esynOpts.projectid != -1 || window.esynOpts.publishedid != -1){
		load_from_server()	
	} else {
		//otherwise we are starting a new prject, get a name
		
		window.currentNwName = getDefaultNwName();
		document.getElementById('nw_name').value = window.currentNwName;
	
	};
	//initialise the navigator overview panel
	$('#cy').cytoscapeNavigator({
    container: false,
    viewLiveFramerate: 0,
    thumbnailEventFramerate: 30,
    thumbnailLiveFramerate: false,
    dblClickDelay: 2
})
  }
});


var cy = $("#cy").cytoscape("get") //get global reference to $cy to use for click events etc

//printState();


//ONCLICK EVENTS - ADD NODES AND EDGES
//use a cy.one function that will fire the first time the background is clicked
cy.one('click', function(evt){
  console.log('User interaction detected, add confirmation on page exit');
  window.onbeforeunload = confirmOnPageExit;
});
//onclick for nodes
cy.on('tap', 'node', function(evt){

  var node = evt.cyTarget;
  console.log( 'tapped ' + node.id() ); //node.id() is a shortcut to get the id
  
  //if in edge mode, check whether sourcenode set, if so then draw an edge between the selected node and the sourcenode then clear sourcenode
  if(window.nmode==false && window.currentNwName != 'Merge-result'){
	  if(window.sourcenode != -1){
		//this is the second node so draw an edge
		//group: "edges", data: { id: "e0", source: "n0", target: "n1" }
		//check that the source and target nodes have different classes
		if(node.hasClass(window.sourcetype)){
			//the target has the same class so cancel edge creation
			cancel_edge();
			alert("Can't create an edge between two nodes of the same type or create a self-loop")	
		} else {
			//the source and target are different types so can create the edge
			//first check whether the edge already exists
			var exists = cy.filter(function(i, element){
				if( element.isEdge() && element.data("source") == window.sourcenode && element.data("target") == node.id() && element.hasClass(window.etype) ){
					return true;
					}
					return false;
				});
		
			if(exists.length == 0){
				var setID = window.stack.metadata.edgecounter.toString();
				cy.add([{
					group: "edges",
					data: {
						id: "e" + setID,
						source: window.sourcenode,
						target: node.id(),
						multiplicity: 1 //initialise as 1. Both normal and inhibitor edges can have a multiplicity greater than 1 
					},
					classes: window.etype
				}])  
				clear();
				window.sourcenode = -1;
				window.stack.metadata.edgecounter += 1;
			} else {
				alert('Duplicate edges are not allowed')	
			}
	  }
	  }else{
		//this is the first node
		//if creating an inhibitor edge, the first node can't be a transition (inhibitor edges can only go place -> transition)
		if( window.etype == 'inhibitor' && node.hasClass('transition')){
			alert('Inhibitor edges cannot have a transition as the source node.')	
		} else {
			//the clicked node is a place so we can start creating an edge
			window.sourcenode = node.id()  
			window.sourcetype = node.hasClass('place') ? 'place' : 'transition' //if it is a place, window.sourcetype = 'place', else 'transition'
			clear();
			print('Creating ' + window.etype + ' edge from: ' + node.data('name'));
			//create a button that will allow the user to cancel edge creation
			print('<button type="button" id="cancel" class="btn btn-danger" onclick="cancel_edge()">Cancel edge creation</button>')
			}
	  }
  } else{
	//in node mode, click a node to display and edit info
	display_info(node);
  }
  
});

//special event for two-finger tap or right click, jump to contained network for coarse node
cy.on('cxttap', 'node', function(evt){
	console.log('cxttap')
	var node = evt.cyTarget;
	if(node.hasClass('coarsetransition') && window.stack.hasOwnProperty(node.data('name'))){
		goTo(node.data('name'))
	}

})

function display_info(node){
	//print information to the #info div. call after modifying a node to update the div
	clear()
  	//print("Selected: " + node.id())
  	if(window.currentNwName != 'Merge-result'){
	  	if(node.hasClass('place')){
			print('<form onkeypress="return event.keyCode != 13;">Name<sup><button type="button" class="btn btn-link btn-xs btn-tight" data-toggle="modal" data-target="#namingModal">[?]</button></sup>:<br /><input type="text" name="set_node_name" id="set_node_name" value ="'+node.data('name')+'"></form>')
			print('<form onkeypress="return event.keyCode != 13;">Marking:<br /><input type="text" name="set_marking" id="set_marking" value ="'+node.data('marking')+'"></form>')
			print('<button type="button" id="set" class="btn btn-success" onclick="set(' + "'" + node.id() + "'"+ ')">Set</button>')
			
			//adding classes to places
			//print('<button type="button" id="edit_coarse_place" class="btn btn-default" onclick="change_coarse(' + "'" + node.data('name') + "'"+ ')">Edit as coarse place</button>')
			//fill in the #contains div
			change_coarse(node.data('name'));

			//fill in the #isa div
			change_parents(node.data('name'));
			//linkout to ensembl
			  //link element
			  //print('<a href="http://www.ensembl.org/Multi/Search/Results?q='+node.data('name')+';site=ensembl", target="_blank">Search Ensembl for this gene</a>'); 
			  //form element
			  print('<form target="_blank" action="http://www.ensembl.org/Multi/Search/Results"><input type="hidden" name="q" value="'+node.data('name')+'" /><input type="hidden" name="site" value="ensembl_all" /><button type="submit" class="btn btn-primary">Search Ensembl</button></form>');

	 	} else {
			print('<form onkeypress="return event.keyCode != 13;">Name:<br /><input type="text" name="set_node_name" id="set_node_name" value ="'+node.data('name')+'"></form>')
			if(!window.stack.hasOwnProperty(node.data('name'))){
				var k = window.stack.metadata.k.hasOwnProperty(node.data("name")) ? window.stack.metadata.k[node.data('name')] : 1;
				print('K:<br /><input type="text" name="set_k" id="set_k" value ="'+k+'">')
			}
			print('<button type="button" id="setid" class="btn btn-success" onclick="set(' + "'" + node.id() + "'"+ ')">Set</button>')
			
			//if the transition doesn't contain a network, show a button to put a network inside, otherwise show a button to load that network
			//button to nest a network inside the selected transition
			if(window.stack.hasOwnProperty(node.data('name'))){
				print('<button type="button" id="jump" class="btn btn-primary" onclick="goTo(' + "'" + node.data('name') + "'"+ ')">Go to network</button>')
			} else {
				print('<button type="button" id="nest" class="btn btn-primary" onclick="nestWithin(' + "'" + node.data('name') + "'"+ ')">Nest network inside</button>')
			};

			buildCitationInterface(node.id());


			buildCitationList(node.id());
	  } 
	  
	  //option to remove the selected node
	  print('<button type="button" id="remove" class="btn btn-danger" onclick="remove_element(' + "'" + node.id() + "'"+ ')">Delete</button>')

} else { //if the current network is a merge result
	print('<form onkeypress="return event.keyCode != 13;">Name<sup><button type="button" class="btn btn-link btn-xs btn-tight" data-toggle="modal" data-target="#namingModal">[?]</button></sup>:<br /><input type="text" name="set_node_name" id="set_node_name" value ="'+node.data('name')+'"></form>')
	if(node.hasClass('place')){
		print('<form onkeypress="return event.keyCode != 13;">Marking:<br /><input type="text" name="set_marking" id="set_marking" value ="'+node.data('marking')+'"></form>')
		//linkout to ensembl	  
		print('<form target="_blank" action="http://www.ensembl.org/Multi/Search/Results"><input type="hidden" name="q" value="'+node.data('name')+'" /><input type="hidden" name="site" value="ensembl_all" /><button type="submit" class="btn btn-primary">Search Ensembl</button></form>');

		//change coarse node tools to show a message
		if($('#add_coarse').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
			document.getElementById('contains').innerHTML += '<div id="add_coarse"></div>';
		};
		$('#add_coarse').html('<p class="bg-warning">Save merge result as new project to edit</p>')

		//add isa relationship
		if($('#add_isa').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
			document.getElementById('isa').innerHTML += '<div id="add_isa"></div>';
		};
		$('#add_isa').html('<p class="bg-warning">Save merge result as new project to edit</p>')

	} else {
		if(!window.stack.hasOwnProperty(node.data('name'))){
			var k = window.stack.metadata.k.hasOwnProperty(node.data("name")) ? window.stack.metadata.k[node.data('name')] : 1;
			print('K:<br /><input type="text" name="set_k" id="set_k" value ="'+k+'">')

			print('Citations:')
		    print('<div class="control-group"><div class="controls"><select id="selectcitation" name="selectcitation" class="input-sm wide" multiple="multiple"></select></div></div>')
		    print('<div class="control-group"><button id="gocitation" class="btn btn-primary" onclick="goToCitation()">Go to selected</button></div>')
		    buildCitationList(node.id());


		    
		}
	}
	print('<p class="bg-warning">Save merge result as new project to edit</p>')
}
  
};

//function to set node values - same function for places and transitions
function set(nodeID){
	var newName = document.getElementById('set_node_name').value //the name can be set for both places and transitions
	//check the name is allowed
	//name != 'nodes'
	if(newName == 'nodes'){
		//this name is not allowed, used in network upload to determine whether the file is a single graph or a stack
		alert('The name "nodes" is not allowed')
	} else if(/^n[0-9]+[^?!() A-Za-z]+$/.test(newName) == true){ //the space in the NOT set is important
		alert("Names of the format 'n followed by digits' are reserved for programmatic use only.")

	} else {
		var change = cy.$('#'+nodeID); //get a reference to the node to be changed
		//check if it is a place or transition
		if(change.hasClass('place')){ 
		//action if it's a place
			var newMarking = parseInt(document.getElementById('set_marking').value)
			if(newMarking < 0){
				alert('marking must be >= 0, not changing anything')
			} else {
				//marking is allowed and k is allowed
				//if the name has already been used, it will become a disperse place
				
				if(newName in window.stack.metadata.transition){
					//the name is not allowed because places and transitions can't have the same names
					alert('The name "' + newName + '" is already in use for a transition. Please choose another name.')
				} else {
					//the name is allowed, check whether it has already been used to sort out namem marking and k

					if(newName in window.stack.metadata.place){ //the name has already been used - either by this node and we should just update it or by a different node and we should make this node an occurence of a disperse place
						if(newName == change.data('name')){ //if the name field hasn't been changed, update the marking and k
							//if newName is not already a disperse place, just update it. Otherwise update all members
							if(newName in window.stack.metadata.disperse){
								//update the marking for all occurences of the disperse place
								console.log('changing marking for all occurences of the disperse place: ' + newName);
								change_disperse_marking(newName, newMarking);

								
							} else { //a normal place so just change the marking, the name is the same
								change.data('marking',newMarking);
								window.stack.metadata.place[newName] = newMarking;	
							}
							
						} else{ //the name has been set to a name already in use
							//creating a disperse place
							//set the name and update the marking for all apperances
							if(change.data('name') in window.stack.metadata.disperse){
								//trying to make all members of a disperse place into members of another existing place
								alert("adding to disperse places this way around is not possible")
								
								//temporarily rename the place that all members of the disperse place will become members of
								//the problem is it might not be found in the current network so would have to search for it,
								//get its marking
								//also if the other place is already a disperse place I need to change all of their names
								//change_place(other place, '__TEMPORARY_NAME__',otherplace.data('marking'))
								//OR
								//change_disperse_name(other place name, '__TEMPORARY_NAME__')
								
								//now rename change the current disperse place to have the original name and marking of the new place
								//change_disperse_name(change.data('name'),newName)
								//change_disperse_marking(change.data('name'), marking of the other place)
								
								//now change the original place back
								//make_disperse(other place, newName)
								
								
							} else {
								alert('you have created an apperance of a disperse place') //swich to alerting function with option to disable
								//update disperse metadata
								make_disperse(change, newName); //this automatically deletes data on the old name from the metadata. don't need to pass in new k as the node itself doesn't store k
							
							}
							
						}
					} else { //the name hasn't been used yet
						//if the selected place is a disperse place, decide whether to rename them all or just this one
						if(change.data('name') in window.stack.metadata.disperse){
							var changeall = confirm('Press OK to use this name for all occurences of this place or cancel to rename this place only, making it separate from the disperse place')
							
							if(changeall){ //apply the changes to all members
								change_disperse_marking(change.data('name'),newMarking);
								change_disperse_name(change.data('name'),newName);
								
							} else { //apply to this member onle i.e. make it no longer a disperse place
								//rename this place and remove it from the list of places associated with the disperse place
								make_not_disperse(change,change.data('name'),nodeID,newName,newMarking);
							
							}
						} else { 
							//These can both be true at the same time:
							//if the selected place contains other places, we have to update the contains metadata
							//if the selected place is containd by other places, we have to update the isa metadata
						
							//just a normal place
							update_place(change,newName,newMarking);
						
						}
						
					}
					
				}
			}
			//now deal with transition
		} else {
			//transition
			//check if the name changed, if not just update k
			if(newName == change.data('name')){
				var newK = parseFloat(document.getElementById('set_k').value)
				if(newK > 0){
					update_k(newName, newK) //we aren't changing the name so updating k is the same for normal places and disperse places			
				} else {
					alert('k must be > 0')
				}
			} else {
				if(newName in window.stack.metadata.place | newName in window.stack.metadata.transition){
					alert('The name "' + newName + '" is already in use. Please choose another name.')	
				} else if(newName == window.currentNwName){
					alert("A transition can't contain the network it's in!")
				} else {
					
					//if the name was the same as a network in the stack but now isn't, alert the user that they are making a transition not coarse and remove the visual style class
					if(change.data('name') in window.stack){
						alert('The selected node will no longer contain the network: ' + change.data('name'))
						change.removeClass('coarsetransition');
					};
					
					//if the name is the same as a network in the stack, add the visual style class
					if(newName in window.stack){
						alert('You have created a coarse transition containing the network: ' + newName);
						change.addClass('coarsetransition');
						//remove k for coarse transition
						if(window.stack.metadata.k.hasOwnProperty(change.data('name'))){
							delete window.stack.metadata.k[change.data('name')];
						}
					} else {
						//the name is ok and we aren't creating a coarse transition
						//update k
						var newK = parseFloat(document.getElementById('set_k').value)
						if(newK > 0){
							update_k(newName, newK) //we aren't changing the name so updating k is the same for normal places and disperse places			
						} else {
							alert('k must be > 0')
						}
					}
					window.stack.metadata.transition[newName] = window.stack.metadata.transition[change.data('name')]
					delete window.stack.metadata.transition[change.data('name')]
					change.data('name', newName);	

				
				}
			}
		}
		
	}
	
	//recently added, make sure it doens't create problems
	display_info(cy.filter("node[id = '" + nodeID+"']"))
	
	//update style - could add to each individual function? now this will duplicate work sometimes
	remove_style();
	update_style();

};

//function to rename and change marking for a normal place
function update_place(node,newName,newMarking){
	//at the point this function is called, we know the place is not disperse
	//delete the old data from the metadata
	delete window.stack.metadata.place[node.data('name')];
	var oldName = node.data('name');
	
	//if the node is coarse, update the metadata.contains
	//copy the data under the old name into a new entry under the new name, delete the data under the old name
	if(oldName in window.stack.metadata.contains){
		var contained = window.stack.metadata.contains[oldName];
		window.stack.metadata.contains[newName] = contained;
		delete window.stack.metadata.contains[oldName];	
		
		//update the isa data for all places contained by this place
		for(var i=0; i<contained.length; i++){
			var old = window.stack.metadata.isa[contained[i]];
			old.splice(old.indexOf(oldName),1);
			old.push(newName);
			window.stack.metadata.isa[contained[i]] = old;
		};
	}
	
	//if the place is contained by other places, update the metadata.isa AND update the contains metadata for those places
	if(oldName in window.stack.metadata.isa){
		var contained_by = window.stack.metadata.isa[oldName];
		
		//replace the entry for the selected node
		window.stack.metadata.isa[newName] = contained_by;
		delete window.stack.metadata.isa[oldName];	
		
		//update the contains metadata for all places that contain the selected place
		for(var i=0; i<contained_by.length; i++){
			var old = window.stack.metadata.contains[contained_by[i]];
			old.splice(old.indexOf(oldName),1);
			old.push(newName);
			window.stack.metadata.contains[contained_by[i]] = old;
		};
	};
	
	//update the node itself
	node.data('name', newName);
	node.data('marking',newMarking);
	
	//create the new metadata
	window.stack.metadata.place[newName] = newMarking;
};

//////////////functions to update parameter k
//update for normal places or to change k for all disperse places
function update_k(name, newK){
	//if k is anything other than 1, update the metadata value
	//otherwise, if K is one we delete the value as k defaults to 1
	if(newK > 0){
		window.stack.metadata.k[name] = newK;
	} else if(newK == 1) {
		if(window.stack.metadata.k.hasOwnProperty(name)){
			delete window.stack.metadata.k[name];
		}
	}
}

//This function generates the GUI used for editing coarse places, it doesn't actually do the editing
function change_coarse(coarse_node_name){
	
	//add place it contains
	if($('#add_coarse').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
		document.getElementById('contains').innerHTML += '<div id="add_coarse"></div>';
	};
	var pselect = $('#add_coarse')
	
	//if the selected node is already contained by some other places, get a list of them and prevent them appearing as options
	var exclude = [coarse_node_name];
	if(coarse_node_name in window.stack.metadata.isa){
		exclude = exclude.concat( window.stack.metadata.isa[coarse_node_name]	); //exclude places that contain the selected node
	};
	if(coarse_node_name in window.stack.metadata.contains){
		exclude = exclude.concat(window.stack.metadata.contains[coarse_node_name]) //exclude places it already contains
	}

	var allnodes = _.keys(window.stack.metadata.place);
	var possible = _.difference(allnodes, exclude);
	var possible = possible.sort(function(a, b) { //case-insensitive sort
					    if (a.toLowerCase() < b.toLowerCase()) return -1;
					    if (a.toLowerCase() > b.toLowerCase()) return 1;
					    return 0;
					  });


	var pOpts = 'Add child place:<select id="to_add"><option selected="selected" value="">Select a place</option>'

	var opts = possible.map(function(name){
		return '<option value ="' + name + '">' + name + '</option>'
	})
	pOpts += opts.join('')

	pOpts += '</select>'
	
	//add button to make the change - changed to ownName from coarse_node_id
	pOpts += '<button class="btn btn-default" onclick="place_contains_button(' + "'" +coarse_node_name +"'" +')">Add</button>'
		
	pselect.html(pOpts)
	
	//////////////////////////////
	//remove places it contains
	if(coarse_node_name in window.stack.metadata.contains){
		if($('#remove_coarse').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
			document.getElementById('contains').innerHTML += '<div id="remove_coarse"></div>';
		};
		
		var pselect = $('#remove_coarse')
		
		var pOpts = 'Child places:<select id="to_remove"><option selected="selected" value="">Children</option>'
		var contained = window.stack.metadata.contains[coarse_node_name];
		var contained = contained.sort(function(a, b) { //case-insensitive sort
					    if (a.toLowerCase() < b.toLowerCase()) return -1;
					    if (a.toLowerCase() > b.toLowerCase()) return 1;
					    return 0;
					  });
		for(var i=0; i<contained.length; i++ ){
			bit = '<option value ="' + contained[i] + '">' + contained[i] + '</option>'
			pOpts += bit
		};
		pOpts += '</select>'
		
		//add button to make the change
		pOpts += '<button class="btn btn-danger" onclick="place_notcontain_button(' + "'" +coarse_node_name +"'" +')">Remove</button>'
			
		pselect.html(pOpts)
	} else {
		if($('#remove_coarse').length > 0){
			//the node is not coarse but there is something in the remove_coarse div, which shouldn't be there
			$('#remove_coarse').remove();
		}

	}
	
};

function change_parents(isa_node_name){
	//this function is used to remove a parent-child relationship when the user starts from selecting the child node
	//first we populate a dropdown list showing the places that contain the selected place

	//add isa relationship
	if($('#add_isa').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
		document.getElementById('isa').innerHTML += '<div id="add_isa"></div>';
	};
	var pselect = $('#add_isa')
	
	//if the selected node is already contained by some other places, get a list of them and prevent them appearing as options
	var exclude = [isa_node_name];
	if(isa_node_name in window.stack.metadata.isa){
		exclude = exclude.concat( window.stack.metadata.isa[isa_node_name]	); //exclude places that contain the selected node
	};
	if(isa_node_name in window.stack.metadata.contains){
		exclude = exclude.concat(window.stack.metadata.contains[isa_node_name]) //exclude places that are already contained by the selected place
	}

	var allnodes = _.keys(window.stack.metadata.place);
	var possible = _.difference(allnodes, exclude);
	var possible = possible.sort(function(a, b) { //case-insensitive sort
					    if (a.toLowerCase() < b.toLowerCase()) return -1;
					    if (a.toLowerCase() > b.toLowerCase()) return 1;
					    return 0;
					  });
	
	var pOpts = 'Add parent place:<select id="parent_to_add"><option selected="selected" value="">Select a place</option>'
	
	var opts = possible.map(function(name){
		return '<option value ="' + name + '">' + name + '</option>'
	})
	pOpts += opts.join('')
	pOpts += '</select>'
	

	//add button to make the change - changed to ownName from coarse_node_id
	pOpts += '<button class="btn btn-default" onclick="place_containedby_button(' + "'" +isa_node_name +"'" +')">Add</button>'
		
	pselect.html(pOpts)

	//remove isa relationship - list places that currently contain this place
	if(isa_node_name in window.stack.metadata.isa){
		if($('#remove_isa').length == 0){ //so the function can be called to update the display area, only create the element if it doesn't exist yet
			document.getElementById('isa').innerHTML += '<div id="remove_isa"></div>';
		};
		
		var pselect = $('#remove_isa')
		
		var pOpts = 'Parent places:<select id="parent_to_remove"><option selected="selected" value="">Parents</option>'
		var contained_by = window.stack.metadata.isa[isa_node_name];
		contained_by = contained_by.sort(function(a, b) { //case-insensitive sort
					    if (a.toLowerCase() < b.toLowerCase()) return -1;
					    if (a.toLowerCase() > b.toLowerCase()) return 1;
					    return 0;
					  });
		for(var i=0; i<contained_by.length; i++ ){
			bit = '<option value ="' + contained_by[i] + '">' + contained_by[i] + '</option>'
			pOpts += bit
		};
		pOpts += '</select>'
		
		//add button to make the change - the parameter is the currently selected node
		pOpts += '<button class="btn btn-danger" onclick="place_notcontainedby_button(' + "'" +isa_node_name +"'" +')">Remove</button>'
			
		pselect.html(pOpts)
	} else {
		if($('#remove_isa').length > 0){
			//the node is not coarse but there is something in the remove_coarse div, which shouldn't be there
			$('#remove_isa').remove();
		}

	}
}

//////////////////////////////////////////////////////////////////
//functions for creating and manipulating disperse places

function make_disperse(node, becomes_name){ //orig is the ID, becomes is the NAME of the disperse place
	//update the visual style
	node.addClass('disperse')
	if(becomes_name in window.stack.metadata.contains){
		//if the disperse place name contains other places, add that class to this node too
		node.addClass('contains');	
	}

	////////////////////////// the first thing to do is clean up old data about the selected place - BEFORE we modify it
	//the original node is effectively deleted, remove any contains/ isa relationships it had
	//use the code from remove_element
	var deleted_name = node.data('name')
	//if the place is a coarse place, now it doesn't contain anything
	if(deleted_name in window.stack.metadata.contains){
		console.log('coarse place, name: '+deleted_name+ ' id: ' + node.data('id') + 'made into a disperse place, so removing its coarse metadata');
		//we deleted an element that contains other element(s) - remove that relationship/ those relationships
		var children = window.stack.metadata.contains[deleted_name] //we will need to update isa for these
		delete window.stack.metadata.contains[deleted_name]
		//now update the isa relationships
		for(var j = 0; j<children.length; j++){
			//for each child node, remove the isa relationship to the deleted node
			window.stack.metadata.isa[children[j]].splice(window.stack.metadata.isa[children[j]].indexOf(deleted_name),1)
			if(window.stack.metadata.isa[children[j]].length == 0){
				delete 	window.stack.metadata.isa[children[j]]; //if we deleted the only place containing the child place then delete its entry in the isa object
			};
		};
	};
	
	//if the place is contained by other places, now they don't contain it
	if(deleted_name in window.stack.metadata.isa){
		console.log('child node, name: '+deleted_name+ ' id: ' + node.data('id') + 'made into a disperse place, removing old metadata about places containing it');
		//get the parent nodes for this place and remove the contains relationship from them
		var parents = window.stack.metadata.isa[deleted_name];
		for(var j=0; j<parents.length; j++){
			window.stack.metadata.contains[parents[j]].splice(window.stack.metadata.contains[parents[j]].indexOf(deleted_name),1)
			if(window.stack.metadata.contains[parents[j]].length == 0){
				//the parent place no longer contains anything, so is not coarse
			
				delete window.stack.metadata.contains[parents[j]];	
			} //end if the parent is no longer coarse
			
		}; //end loop through parents
		
		delete window.stack.metadata.isa[deleted_name];	
	}; //end sorting out isa

	
	/////////////////now make it a disperse place
	
	//get the current disperse places
	var disp = Object.keys(window.stack.metadata.disperse);
	//if becomes is already a disperse place, we are adding a new member. Otherwise we are creating a new disperse place
	if(disp.indexOf(becomes_name) >= 0){
		//adding to an existing disperse place 
		//delete orig from places
		delete window.stack.metadata.place[node.data('name')]
		//set the name of orig to newname
		node.data('name',becomes_name)
		//set the marking of orig to the marking of the disperse place
		node.data('marking', window.stack.metadata.place[becomes_name])
		//update disperse place metadata
		if(Object.keys(window.stack.metadata.disperse[becomes_name]).indexOf(window.currentNwName) >= 0){
			//there is already a reference to the disperse place in this network, append the new one
			window.stack.metadata.disperse[becomes_name][window.currentNwName].push(node.id())
			
		} else {
			window.stack.metadata.disperse[becomes_name][window.currentNwName] = [node.id()]
		};
		
	} else {
		//creating a new disperse place
		
		//find the node that the selected node is becoming
		var allNwNames = Object.keys(window.stack)
		allNwNames.splice(allNwNames.indexOf('metadata'),1) //remove metadata from the list of networks
		if(allNwNames.indexOf(window.currentNwName) >=0){//don't search the current network as it might have been modified
			allNwNames.splice(allNwNames.indexOf(window.currentNwName),1) 
		};
		
		//we need to find the name of the network that contains the other appearance of the newly created disperse place
		//for now this has to be done by searching for it, it could be in any of the networks in the stack
		var found_in = '';
		var found_id = '';
		var changeclass = false;
		//this code only executes when you're first creating a disperse place
		//we know that the selected node is in the current network but the other member could be anywhere
		//have to search all other networks until we find it, and then update its classes and the metadata
		for(var i=0; i<allNwNames.length; i++){
			var tmp = JSON.parse(window.stack[allNwNames[i]]); //load network data - doesn't get displayed
			var tmp_n = Object.keys(tmp.nodes)
			for(var j=0; j<tmp_n.length;j++){ //search for a node with the same name
				if(tmp.nodes[tmp_n[j]].data.name == becomes_name){
					console.log(becomes_name + ' found in:' + allNwNames[i]);
					found_in = allNwNames[i];
					found_id = tmp.nodes[tmp_n[j]].data.id;
					
					//don't need to udpate the visual style class as it isn't in the currently loaded network
				}
			};
		};
		
		//if we haven't found it by searching all other networks then it must be in the current network 
		if(found_in == ''){
			//alert('not found in any other network, must be in current network')
			var bec = cy.filter("node[name = '" + becomes_name+"']")
			if(bec.length == 1){
				found_in = window.currentNwName;
				found_id = bec.id()
				//update the classes
				bec.addClass('disperse')
			} else if(bec.length > 1) {
				alert('more than one matching original node found in the current network')	
			} else {
				alert('There is an error in the data for this project, please contact an administrator to repair the project. Error: There is metadata for a place that cannot be found')
			}
		};
		
		//now the code below is all that's needed to update the visual style in the current network
		var bec = cy.filter("node[name = '" + becomes_name+"']"); //if there is another appearance in the current network, this will update its visual style class, otherwise it won't do anything
		bec.addClass('disperse');
		
		//delete orig from places
		delete window.stack.metadata.place[node.data('name')]
		//set the name of orig to newname
		node.data('name',becomes_name)
		//set the marking of orig to the marking of the disperse place
		node.data('marking', window.stack.metadata.place[becomes_name])
		//update disperse place metadata
		window.stack.metadata.disperse[becomes_name] = {};
		window.stack.metadata.disperse[becomes_name][window.currentNwName] = [node.id()]
		if(window.currentNwName == found_in){ //if they are both in the same network then push both IDs
			window.stack.metadata.disperse[becomes_name][found_in].push(found_id)
		} else {
			window.stack.metadata.disperse[becomes_name][found_in] = [found_id]
		};
		
	};
	
	
	
};

function make_not_disperse(change, disperse_name, remove_id, newName, newMarking){
	//change = the node to change
	//disperse = NAME of the disperse place, remove_id = the ID of the place to be removed from the group, newName = the new name for the place remove_id, newMarking = the marking for the place remove_id
	//this function will make the given node NOT a member of a disperse place any more
	//if the node was one of only two members, the original member will also be reset to a normal place
	
	// DON'T USE update_place - it will delete the reference to other members of the disperse place from the place metadata!
	//update the selected place
	change.data('name', newName);
	change.data('marking',newMarking);
	//remove the visual style
	change.removeClass('disperse');
	
	//create the new metadata
	window.stack.metadata.place[newName] = newMarking;
	
	////// check whether the current network contains any other appearances of the disperse place
	///// if it doesn't then remove it from the list of networks that contain the disperse place
	//we know that the selected node is in the currently viewed network
	var thisNwMembers = window.stack.metadata.disperse[disperse_name][window.currentNwName]
	//if this node is the only appearance in the current network, delete the current network from the list
	//otherwise only delete the reference to this node
	if(thisNwMembers.length > 1){
		thisNwMembers.splice(thisNwMembers.indexOf(remove_id),1);
		window.stack.metadata.disperse[disperse_name][window.currentNwName] = thisNwMembers;	
	} else { //the selected node is the only appearance of the disperse place in the current network so just delete the current network from the list of networks that contain an appearance of the disperse place
		delete window.stack.metadata.disperse[disperse_name][window.currentNwName];
	}
	
	/////// check whether there is now only one appearance of the disperse place left - i.e. it is not disperse any more
	//check whether the disperse place is still disperse after the removal of the selected node
	var allMembers = window.stack.metadata.disperse[disperse_name];
	var allMembersNws = Object.keys(window.stack.metadata.disperse[disperse_name]);
	if(allMembersNws.length == 1){
		//must be disperse if there is more than one network that contains an appearance of the place
		//if there is only one, check how many references it contains to the disperse place
		//if it's only one then it is no longer a disperse place
		if(allMembers[allMembersNws[0]].length == 1){ //only one network contains a reference to this place and that network only contains one reference to it -> not a disperse place any more
			//no longer a disperse place
			//the remaining nodes name will still connect it to window.stack.metadata.place
			delete window.stack.metadata.disperse[disperse_name];
			
			/* no longer needed - visual style classes are no longer saved
			/////////////////////
			//new bit to remove class - need to check whether the remaining node is in the current network
			//if so then use cy removeClass, otherwise update the stack
			//update the classes for the remaining node
			var otherplace_nw = JSON.parse(window.stack[allMembersNws[0]]);
			var otherplace_nodes = otherplace_nw.nodes;
			for(var j = 0; j<otherplace_nodes.length; j++){ //we know there is only one node that will match
				if(otherplace_nodes[j].data.name == disperse_name){
					var oldclass = otherplace_nodes[j].classes;
					var newclass = oldclass.replace(' disperse','') //note preceeding space - otherwise they'll accumulate
					otherplace_nw.nodes[j].classes = newclass;
				};
			};
			window.stack[allMembersNws[0]] = JSON.stringify(otherplace_nw)//update the stack
			*/
			
			//if there is an appearance of the disperse place in the current network then update its visual style classes
			var toupdate = cy.filter("node[name = '" + disperse_name+"']");
			toupdate.removeClass('disperse');
		};
		
	};
};

function change_disperse_marking(name,newMarking){
	//track down all members of the disperse place and change their marking
	var nwToLoad = Object.keys(window.stack.metadata.disperse[name]); //this is an array of network names
	
	//remove the current network and edit separately
	if(nwToLoad.indexOf(window.currentNwName) >= 0){ //might not be saved to the stack yet
		nwToLoad.splice(nwToLoad.indexOf(window.currentNwName),1);
	};
	var tochange = window.stack.metadata.disperse[name][window.currentNwName] //nodes to update in the current network
	for(var i=0; i<tochange.length; i++){
		console.log('changing marking in current network')
		cy.$('#' + tochange[i]).data('marking',newMarking)	
	};
	
	//load each of the networks and edit the nodes based on ID
	for(var i=0; i<nwToLoad.length; i++){
		var tmp = JSON.parse(window.stack[nwToLoad[i]]); //load network data - doesn't get displayed
		var tmp_n = Object.keys(tmp.nodes)
		for(var j=0; j<tmp_n.length;j++){ //search for a node with the same name
			if(window.stack.metadata.disperse[name][nwToLoad[i]].indexOf(tmp.nodes[tmp_n[j]].data.id) >= 0){
				console.log(name + ' found in:' + nwToLoad[i] + ", changing multiplicity");
				tmp.nodes[tmp_n[j]].data.marking = newMarking;
			};
		};
		//replace the stack data
		window.stack[nwToLoad[i]] = JSON.stringify(tmp)
	};
	
	//update the global places marking
	window.stack.metadata.place[name] = newMarking;
};

function change_disperse_name(oldName,newName){
	//track down all members of the disperse place and change their name
	var nwToLoad = Object.keys(window.stack.metadata.disperse[oldName]); //this is an array of network names
	
	//remove the current network and edit separately
	if(nwToLoad.indexOf(window.currentNwName) >= 0){ //might not be saved to the stack yet
		nwToLoad.splice(nwToLoad.indexOf(window.currentNwName),1);
	};
	var tochange = window.stack.metadata.disperse[oldName][window.currentNwName]; //nodes to update in the current network
	for(var i=0; i<tochange.length; i++){
		console.log('changing name in current network')
		cy.$('#' + tochange[i]).data('name',newName)	
	};
	
	//load each of the networks and edit the nodes based on ID
	for(var i=0; i<nwToLoad.length; i++){
		var tmp = JSON.parse(window.stack[nwToLoad[i]]); //load network data - doesn't get displayed
		var tmp_n = Object.keys(tmp.nodes);
		for(var j=0; j<tmp_n.length;j++){ //search for a node with the same name
			if(window.stack.metadata.disperse[oldName][nwToLoad[i]].indexOf(tmp.nodes[tmp_n[j]].data.id) >= 0){
				console.log(oldName + ' found in:' + nwToLoad[i] + ", changing name");
				tmp.nodes[tmp_n[j]].data.name = newName;
			};
		};
		//replace the stack data
		window.stack[nwToLoad[i]] = JSON.stringify(tmp)
	};
	
	//update the global places marking
	var marking = window.stack.metadata.place[oldName];
	window.stack.metadata.place[newName] = marking;
	delete window.stack.metadata.place[oldName];
	
	//update the disperse place metadata so that the new name is listed as a disperse place
	window.stack.metadata.disperse[newName] = window.stack.metadata.disperse[oldName]; //the associated places remain the same
	delete window.stack.metadata.disperse[oldName];

	///////////// update coarse place metadata
	//if the disperse place is coarse, update the metadata.contains
	//copy the data under the old name into a new entry under the new name, delete the data under the old name
	if(oldName in window.stack.metadata.contains){
		var contained = window.stack.metadata.contains[oldName];
		window.stack.metadata.contains[newName] = contained;
		delete window.stack.metadata.contains[oldName];	
		
		//update the isa data for all places contained by this place
		for(var i=0; i<contained.length; i++){
			var old = window.stack.metadata.isa[contained[i]];
			old.splice(old.indexOf(oldName),1);
			old.push(newName);
			window.stack.metadata.isa[contained[i]] = old;
		};
	}
	
	//if the place is contained by other places, update the metadata.isa AND update the contains metadata for those places
	if(oldName in window.stack.metadata.isa){
		var contained_by = window.stack.metadata.isa[oldName];
		
		//replace the entry for the selected node
		window.stack.metadata.isa[newName] = contained_by;
		delete window.stack.metadata.isa[oldName];	
		
		//update the contains metadata for all places that contain the selected place
		for(var i=0; i<contained_by.length; i++){
			var old = window.stack.metadata.contains[contained_by[i]];
			old.splice(old.indexOf(oldName),1);
			old.push(newName);
			window.stack.metadata.contains[contained_by[i]] = old;
		};
	};
	
};

function set_edge(id){
	var newMultiplicity = parseInt(document.getElementById('set_multiplicity').value)
	//multiplicity must be an integer >0. parseInt('string') returns NaN which evaluates as false for > 0
	if(newMultiplicity > 0){
		var change = cy.$('#'+id);
		change.data('multiplicity', newMultiplicity);
	} else {
	 //multiplicity must be an integer > 0
	 alert('Multiplicity must be an integer >0')	
	}
	
};

//remove elements from the graph
function remove_element(id){ //id isn't used, the selected element(s) is/are deleted
	//updated to remove ALL SELECTED ELEMENTS not just the last selected element
	var selected = cy.elements(":selected")
	
	
	//update the metadata

	//for each selected element
	for(var i=0; i<selected.length; i++){
		var el = selected[i]
		//if it's a node
		if(el.data.hasOwnProperty('target') == false){
			if(el.hasClass('place')){
				var deleted_name = el.data('name');
				if(el.data('name') in window.stack.metadata.disperse){
					console.log('deleting disperse node, name: '+el.data('name')+ ' id: ' + el.data('id'));
					//find the reference to this node and delete it
					//we know the selected node is on the currently loaded network
					var current = window.stack.metadata.disperse[el.data('name')][window.currentNwName] //current refs to the disperse place in the current network - if we deleted the final reference then delete the entry for this network
					current.splice(current.indexOf(window.currentNwName),1);
					if(current.length == 0){ //if we deleted the only appearance then delete the current network from the list
						delete window.stack.metadata.disperse[el.data('name')][window.currentNwName];
					} else {
						window.stack.metadata.disperse[el.data('name')][window.currentNwName] = current;
					};
					
					//check whether there is still more than one member of the disperse place, if not then make the only member back into a normal place
					var allMembers = window.stack.metadata.disperse[el.data('name')];
					var allMembersNws = Object.keys(window.stack.metadata.disperse[el.data('name')]);
					if(allMembersNws.length == 1){
					//must be disperse if there is more than one network that contains an appearance of the place
					//if there is only one, check how many references it contains to the disperse place
					//if it's only one then it is no longer a disperse place
						if(allMembers[allMembersNws[0]].length == 1){ //only one network contains a reference to this place and that network only contains one reference to it -> not a disperse place any more
						//no longer a disperse place
						//the remaining nodes name will still connect it to window.stack.metadata.place
						delete window.stack.metadata.disperse[el.data('name')];
					
						};
					
					};
					
				} else {
					console.log('deleting unique (not disperse) place, name: '+el.data('name')+ ' id: ' + el.data('id'));
					//if it isn't a disperse place, we can delete the place from the global list of places
					delete window.stack.metadata.place[el.data('name')]	
					
				//}; - used to end the else here but moved below so that isa/contains only changed for non-disperse places
				//the remaining places should still have the coarse relationship!
				
					//if the deleted place was a normal (unique) place, it now can't contain anythung
					//but if it was a disperse place and there is still at least one place left with the name then the
					//contains/ isa relationships SHOULD NOT be deleted
					//i.e. even if the remaining place is not disperse any more, it should still keep the coarse property
					
					//if the place is a coarse place, now it doesn't contain anything
					if(deleted_name in window.stack.metadata.contains){
						console.log('deleting coarse place, name: '+deleted_name+ ' id: ' + el.data('id'));
						//we deleted an element that contains other element(s) - remove that relationship/ those relationships
						var children = window.stack.metadata.contains[deleted_name] //we will need to update isa for these
						delete window.stack.metadata.contains[deleted_name]
						//now update the isa relationships
						for(var j = 0; j<children.length; j++){
							//for each child node, remove the isa relationship to the deleted node
							window.stack.metadata.isa[children[j]].splice(window.stack.metadata.isa[children[j]].indexOf(deleted_name),1)
							if(window.stack.metadata.isa[children[j]].length == 0){
								delete 	window.stack.metadata.isa[children[j]]; //if we deleted the only place containing the child place then delete its entry in the isa object
							};
						};
					};
				
				}; //end if not disperse - moved here so that contains/isa not removed if a disperse place deleted
				
				//if the place is contained by other places, now they don't contain it
				if(deleted_name in window.stack.metadata.isa){
					console.log('deleting child node, name: '+deleted_name+ ' id: ' + el.data('id'));
					//get the parent nodes for this place and remove the contains relationship from them
					var parents = window.stack.metadata.isa[deleted_name];
					for(var j=0; j<parents.length; j++){
						window.stack.metadata.contains[parents[j]].splice(window.stack.metadata.contains[parents[j]].indexOf(deleted_name),1)
						if(window.stack.metadata.contains[parents[j]].length == 0){
							delete window.stack.metadata.contains[parents[j]];	
						}
					};
					delete window.stack.metadata.isa[deleted_name];	
				};
				
				
					
			//end if place
			} else {
				//transition
				//if it's a coarse transition, prompt to also delete the network it contains
				console.log('deleting transition node, name: '+el.data('name')+ ' id: ' + el.data('id'));
				if(el.data('name') in window.stack){
					alert('You have deleted a coarse transition but not the network it contains')	
				}
				delete window.stack.metadata.transition[el.data('name')]
				if(window.stack.metadata.k.hasOwnProperty(el.data('name'))){
					delete window.stack.metadata.k[el.data('name')];
				}
			}
		} else {
			console.log('element is an edge, id: ',el.data('id'))
		}

	}
	
	
	//delete the elements
	selected.remove();
	
	//clear visual styles then reapply
	remove_style();
	update_style();
	
	//to remove just the last selected element
	//var rm = cy.$('#'+id);
	//cy.remove(rm);
};


//click the background to add a node at that position    
cy.on('click', function (e,nmode) {
    if (e.cyTarget === cy) {
    	if( window.currentNwName != 'Merge-result') {
			if (window.nmode == true){
				//we will use the corrected click location in both modes
				var offset = $("#cy").offset()                   
				var xPos = e.originalEvent.pageX - offset.left;
				var yPos = e.originalEvent.pageY - offset.top;

				if(window.ntype != 'structure'){  
					
					var default_name = "n" + window.stack.metadata['nodecounter']; //also the default id
					var default_marking = 0;
					console.log(xPos.toString(), yPos.toString())
					cy.add([{
						group : "nodes",
						data : {
							id : default_name,
							name : default_name,
							marking : default_marking //always initialise as 0, transitions will have multiplicity = 0, doesn't matter
							
						},
						classes: window.ntype,
						renderedPosition : {
							x : xPos, // x position,
							y : yPos // y position
						},
					}]);
					
					//update counter
					window.stack.metadata['nodecounter'] += 1;
					
					//update node/place name metadata
					window.stack.metadata[window.ntype][default_name] = default_marking;
				} else {
					//creating a structure
					console.log('structure mode')

					//esynOpts contains the details on the module
					addIntoNetwork(window.esynOpts.module.id, window.esynOpts.module.published, xPos, yPos)
				};
			}

		} else {
			clear();
	    	print('<p class="bg-warning">Save merge result as new project to edit</p>')
	    }
    }    
});

//onlick for edges
cy.on('tap','edge', function(evt){
	var edge = evt.cyTarget;
	console.log('tapped ' + edge.id())
	
	clear()
	print("Source: " + cy.filter("node[id = '" + edge.data('source')+"']").data('name'))
	print("Target: " + cy.filter("node[id = '" + edge.data('target')+"']").data('name'))
	
	print('<form onkeypress="return event.keyCode != 13;">Multiplicity:<br /><input type="text" name="set_multiplicity" id="set_multiplicity" value ="'+edge.data('multiplicity')+'"></form>')
	print('<button class="btn btn-success" type="button" id="set" onclick="set_edge(' + "'" + edge.id() + "'"+ ')">Set</button>')
	
	if(edge.hasClass('normal')){
		print("Type: Normal")
		
	} else {
		print("Type: Inhibitor")
	}
	
	print('<button type="button" class="btn btn-danger" id="remove" onclick="remove_element(' + "'" + edge.id() + "'"+ ')">Delete</button>') 

});


// SWITCHING MODE
//buttons to set input type
window.onload=function() {
  var radios = document.type_form.types;
  for (var i = 0; i < radios.length; i++)
    radios[i].onclick=RadioClicked;
}

function RadioClicked() {
	//console.log(this.value)
    if (this.value == "n-p") {
      window.nmode = true
	  window.ntype = 'place'
	  window.sourcenode = -1
	  cancel_edge();
   } else if (this.value == "n-t") {
       window.nmode = true
	  window.ntype = 'transition'
	  window.sourcenode = -1
	  cancel_edge();
   } else if (this.value == "e-n") {
       window.nmode = false
	  window.etype = 'normal'
	  window.sourcenode = -1
	  cancel_edge();
   } else if (this.value == "e-i") {
       window.nmode = false
	  window.etype = 'inhibitor'
	  window.sourcenode = -1
	  cancel_edge();
	} else {
		console.log("error with editor type form - value not recognised")   
   }
   
   //update info panel
   //printState();
}

///////////////////////// ADD AND REMOVE PLACE CLASSES
function place_contains(place, contains){
	//both place and contain are NAMES now
	if(place == contains){ //this is prevented by the gui now
		alert('A place cannot contain itself.')
	} else if(contains == "" || place == ""){
		alert('Not a valid place name')
	} else {
		//the IDs are ok
		if(place in window.stack.metadata.contains){
			window.stack.metadata.contains[place].push(contains);
		} else {
			window.stack.metadata.contains[place] = [contains];
		}
		if(contains in window.stack.metadata.isa){
			window.stack.metadata.isa[contains].push(place);
		} else {
			window.stack.metadata.isa[contains] = [place];
		}
		
		//update visual style - this will catch all occurences of a disperse place in a network because it searches by name
		var toupdate = cy.filter("node[name = '" + place+"']");
		toupdate.addClass('contains'); //add a way to only add the class if it doesn't already have it - can't use cy.filter because you can use hasClass on a collection - so if there is more than one appearance of the dipserse place in the current network it will break
		
		//check the current network separately - it is likely to have been edited since it was loaded from the stack
			
		
		/* this is now not needed because visual style classes are not saved
		//REMOVE THE CURRENT NETWORK FROM THE LIST OF NETWORKS
		//now check whether the place is disperse and if so update any members in other networks
		if(place in window.stack.metadata.disperse){
			var nws_data = window.stack.metadata.disperse[place]; //networks containing nodes to be updated - nodes are IDs
			var nws = Object.keys(nws_data);
			nws.splice(nws.indexOf(window.currentNwName),1);
			for(var i=0; i<nws.length; i++){
				var tmp = JSON.parse(window.stack[nws[i]]);
				for(var j=0; j<tmp.nodes.length; j++){
					if(nws_data[nws[i]].indexOf(tmp.nodes[j].data.id) >= 0){
						//the current node id is in the list of ids that are part of the disperse place in the current network
						var oldclass = tmp.nodes[j].classes;
						var newclass = oldclass + ' contains';
						tmp.nodes[j].classes = newclass;
						changeclass = true;
					};
				};
				if(changeclass){
					window.stack[nws[i]] = JSON.stringify(tmp)
				};
			};
		};
		*/
		
	};
	
}; 

function place_contains_button(place){
	//for use with the gui
	var contains = $('#to_add').val()
	place_contains(place,contains)	
	
	//update the display
	change_coarse(place);
	change_parents(place);
}

//wrapper to add a parent-child relationship when the user starts from the child node
function place_containedby_button(place){
	//the place passed in is the currently selected node - i.e. the child node
	//for use with the gui
	var contained_by = $('#parent_to_add').val()
	place_contains(contained_by, place);	
	
	//update the display
	change_coarse(place);
	change_parents(place);
}

function place_notcontain(place,notcontain){
	if(notcontain == "" || place == ""){
		alert("not a valid place name")
	} else {
		//make a place no longer contain another place
		//both place and notcontain are IDs - but if whole system changed to name it will still work
		//if we deleted the only contained place then remove place from list of coarse places
		window.stack.metadata.contains[place].splice(window.stack.metadata.contains[place].indexOf(notcontain),1);
		if(window.stack.metadata.contains[place].length == 0){
			delete window.stack.metadata.contains[place];
		};
		window.stack.metadata.isa[notcontain].splice(window.stack.metadata.isa[notcontain].indexOf(place),1);
		if(window.stack.metadata.isa[notcontain].length == 0){
			delete window.stack.metadata.isa[notcontain];
		};
		//update visual style - don't need to use the full clear/ update for all nodes here as disperse relationships are not affected
		var toupdate = cy.filter("node[name = '" + place+"']");
		toupdate.removeClass('contains');
		
		/* this is no longer needed because visual style classes are not saved
		
		//if the place is a disperse place, also update the visual style classes for all the other appearances
		if(place in window.stack.metadata.disperse){
			//any appearances in the current network will already have been updated above (because cy.filter used with NAME)
			//check for any appearances in other networks
			var nws_data = window.stack.metadata.disperse[place]; //networks containing nodes to be updated - nodes are IDs
			var nws = Object.keys(nws_data);
			nws.splice(nws.indexOf(window.currentNwName),1);
			for(var i=0; i<nws.length; i++){
				var tmp = JSON.parse(window.stack[nws[i]]);
				for(var j=0; j<tmp.nodes.length; j++){
					if(nws_data[nws[i]].indexOf(tmp.nodes[j].data.id) >= 0){
						//the current node id is in the list of ids that are part of the disperse place in the current network
						var oldclass = tmp.nodes[j].classes;
						var newclass = oldclass.replace(' contains','')
						tmp.nodes[j].classes = newclass;
						changeclass = true;
					};
				};
				if(changeclass){
					window.stack[nws[i]] = JSON.stringify(tmp)
				};
			};
		};
		*/
	};
};

function place_notcontain_button(place){
	//wrapper for place_notcontain so it works with button
	var notcontain = $('#to_remove').val()
	place_notcontain(place,notcontain);
	
	//update the display
	change_coarse(place);
	change_parents(notcontain);
};

//wrapper for place_notcontain used to remove the parent-child relationship when the user selects the child node first
function place_notcontainedby_button(place){
	//wrapper for place_notcontain so it works with button
	var notcontain_by = $('#parent_to_remove').val()
	place_notcontain(notcontain_by, place);
	
	//update the display
	change_coarse(notcontain_by);
	change_parents(place);
};





//DISPLAY INFORMATION IN SIDE PANEL
//function to print app status
function printState(){
	if(window.nmode){
		$("#state").text("Node mode: "+ window.ntype)	
	}
	else{
		$("#state").text("Edge mode: "+ window.etype)	
	}
	
}

//functions for displaying node info
function clear() {
	document.getElementById("info").innerHTML = "";
	document.getElementById('contains').innerHTML = "Edit child nodes";
	document.getElementById('isa').innerHTML = "Edit parent nodes";

	//clear the interactions panel
	document.getElementById('int-results').innerHTML = ''
}
                
function print(msg) {
	document.getElementById("info").innerHTML += "<p>" + msg + "</p>";
}

//SAVE AND UPLOAD GRAPH
//JSON.stringify(cy.json()['elements']) //cy.json() has cyclic references so have to get different parts individually
//I don't think saving the rest is necessary as the style will always be the same

//function to decide what information should be saved and whether the calling function should be executed
//this function will automatically update the network stack as required
//any function that needs to change the data in the viewer should look like:
/*
function changes_view(){
	var exec = check_changes()
	if(exec){
		//what to do if all the changes have been saved correctly or discarded
		cy.load(new_network)
	};
};
*/

//this function is used to decide whether an action that would swap data between the stack and the viewer should be carried out
//will update the stack so all the calling function needs to do is continue to load the new data
function check_changes(){ 
	var name = document.getElementById('nw_name').value
	var do_fn = true //whether or not to actually do the calling function
	
	//check whether the save operation needs to be cancelled
	//cancel if there is a network in the viewer that has not been named and should be saved
	
	//is the network name the default name?
		//yes
			//does the user want to keep the network or not?
				//yes
					//prompt to enter a name, cancel saving
				
				//no
					//don't update stack
					//do execute
		//no
			//is the name already being used?
				//yes
					//overwrite data currently associated with that name with the data from the viewer?
						//yes
							//update stack
							//do execute
						//no
							//save changes under a different name?
								//yes
									//don't update stack
									//don't save
								//no
									//don't update stack
									//do execute
				//no
					//update stack
					//do execute
					
////////////////////////////////////////////////////////////////////////////////////////////////
	
	//is the network name the default name?
	if(name == 'network_name'){
		//yes
			//does the user want to keep the network or not?
			var r = confirm('Do you want to include the current network? (Hit cancel to discard it)')
			if(r == true){
				//yes
					//prompt to enter a name, cancel saving
					alert('Please enter a name for this network in the control panel')
					do_fn = false
			} else {
				//no
					//don't update stack
					//do save
					do_fn = true
			}
	} else {
		//no
			//is the name already being used?
			if(window.stack.hasOwnProperty(name)){
				//yes the name is being used
					//overwrite data currently associated with that name with the data from the viewer?
					var r = confirm('Save changes to network: ' + name + '? (Hit cancel to save changes under a different name or discard changes)' )
					if (r == true){
						//yes do overwrite
							//remove visual styles
							remove_style();
						
							//update stack
							var jdata = cy.json()['elements'];
							if('nodes' in jdata ){ //it would break if you could save an empty network to the stack
								window.stack[name] = JSON.stringify(jdata);
							} else {
								alert('the current network is empty and will not be saved')	
							};
							//save
							do_fn = true
					} else {
						//no don't overwrite
							//save changes under a different name?
							var r = confirm('Save changes to network: ' + name + ' under a different name? (Hit cancel to discard)' )
							if(r == true){
								//yes
									//don't update stack
									//don't save
									do_fn = false
							} else {
								//no
									//don't update stack
									//do save
									var do_fn = true
							}
					}
									
									
			} else {					
				//no
					// remove visual style classes
					remove_style();
					//update stack
					
					var jdata = cy.json()['elements'];
					if('nodes' in jdata){ //it breaks if you can save an empty network to the stack
						window.stack[name] = JSON.stringify(cy.json()['elements']);
					} else {
						alert('the current network is empty and will not be saved')	
					}; 
					//save
					do_fn = true
			}
	}
		
	
	return do_fn //should the calling function be executed
}

function updateStack(){
	//this function replaces the check_changes dialogues - now we always save changes to the stack
	// remove visual style classes
	remove_style();
	//unselect
	var selected = cy.elements(":selected")
	selected.unselect()
	var jdata = cy.json()['elements'];
	if('nodes' in jdata){ //it breaks if you can save an empty network to the stack
		window.stack[window.currentNwName] = JSON.stringify(cy.json()['elements']);
	}
};

//download data - either just the current view or the whole network stack
function save(what){
	if(what == 'view'){
		//convert the current view for saving
		var network_json = JSON.stringify(cy.json()['elements']);
	};
	if(what == 'all'){
		//save the whole stack
		//check whether the current view is stored in the stack
		var name = document.getElementById('nw_name').value
		
		updateStack();
		
		window.stack.metadata = JSON.stringify(window.stack.metadata); //stringify the metadata - don't have to stringify-parse separately but it makes the metadata consistent with the network data
		var network_json = JSON.stringify(window.stack) //this can be parsed back to a functional stack with JSON.parse

		if($('#set_filename_form').length != 0){ //if the project id is not -1 we won't be able to edit the name
			var filename = document.getElementById('set_filename').value;
		} else {
			var currentdate = new Date(); 
			var filename = "Saved project: " + currentdate.getDate() + "/"
            + (currentdate.getMonth()+1)  + "." 
            + currentdate.getFullYear() + " @ "  
            + currentdate.getHours() + ":"  
            + currentdate.getMinutes() + ":" 
            + currentdate.getSeconds();
		}
		
		var blob = new Blob([network_json], {type: "text/plain;charset=utf-8"});
		saveAs(blob, filename +'.esynPetriNet.txt');	
		
		window.stack.metadata = JSON.parse(window.stack.metadata) //reset so it's functional again after saving!
	

	}
};

//function to check uploaded file is ok
//in the models app this is currently just for compatibility with app_petri as the upload form calls validUpload not uploadFile
function validUpload(){
	uploadFile();
}

function uploadFile(){
	//process the selected file, attempt to use it to create a stack
	var fileInput = document.getElementById('file_upload');
	var file = fileInput.files[0];
	if(file.name.indexOf('esyndiagram') >= 0){
		alert("The selected file is a Diagram, please use the Diagrams tool.")
	} else {
		console.log('processing upload of:' + file.name + ' detected type: ' + file.type);
		/*file types:
		.txt -> "text/plain" - i.e. tsv or esyn
		*/

		var reader = new FileReader();
		//define function to run when reader is done loading the file
		reader.onload = function(e) {
			//detect the type of file
			var esynType = 'text/plain';

			if(file.type.match(esynType)){
				var uploaded_json = tryJSON(reader.result); //will be false if the string is not JSON, otherwise will be the JSON data
				//check it looks like an esyn file
				if(uploaded_json.hasOwnProperty('metadata')){
					console.log('esyn file') 
					uploadFromEsyn(uploaded_json)
					$('#myModal').modal('hide');
				} else {
					alert("Error: The selected file was not recognised as an esyN Petri Net project.")
				}
			} else if(file.name.indexOf('.m')>=0){
				//upload from snoopy
				uploadFromSnoopy(reader.result)
				$('#myModal').modal('hide');
			} else {
				alert("Error: The selected file was not recognised as an esyN Petri Net project.")
			}
		}
		
		//now load the files
		reader.readAsText(file);
	}

};

/*
fileInput.addEventListener('change', function(e) {
	var file = fileInput.files[0];
	var textType = /text.*/ /*; 

	if (file.type.match(textType)) {
		var reader = new FileReader();

		reader.onload = function(e) {
			//alert(reader.result)
			var uploaded_json = JSON.parse(reader.result)
			if(uploaded_json.hasOwnProperty('nodes')){
				//the uploaded file is a single network not a stack
				cy.load(uploaded_json) //load and display the network
				cy.layout({name:'preset'})
				console.log('network loaded')
			} else {
				//the file is a network stack
				if(file.name.indexOf('esyndiagram') >= 0){
					alert("This is a Diagram file, please use the Diagrams tool.")
				} else {
					window.stack = uploaded_json
					window.stack.metadata = JSON.parse(window.stack.metadata)
					
					//update the dropdown list of options
					var nwselect = $('#nwlist')
					var nwOpts = '<option selected="selected" value="">Select a network</option>'
					for(val in window.stack){
						if(val != 'metadata'){
							bit = '<option value ="' + val + '">' + val + '</option>'
							nwOpts += bit
						};
					};
						
					nwselect.html(nwOpts)
					
					//hack to get upload to bypass network selection dropdown the first time
					//will be removed when upload is separated from the viewer
					
					var autoselect = Object.keys(window.stack)[1] //[1] not [0] because [0] is the metadata
					cy.load(JSON.parse(window.stack[autoselect])) //load and display the network
					cy.layout({name:'preset'})
					document.getElementById('nw_name').value = autoselect //set to name of current network
					window.currentNwName = autoselect;
					
					//apply the visual style classes
					update_style();
					
					//use something like this to also set the dropdown - need to guarantee it selects the right one, this doesn't fire an onchange
					//document.getElementById("nwlist").selectedIndex = 2;
					console.log('network loaded by autoselect hack')
			
					//printState();
				}
			};
		}

		reader.readAsText(file);	
	} else {
		 alert("File not supported. Must be JSON data as text.")
	}
});
*/

function uploadFromEsyn(uploaded_json){
	console.log('uploadFromEsyn');
	window.stack = uploaded_json
	window.stack.metadata = JSON.parse(window.stack.metadata)
	if(window.stack.metadata.hasOwnProperty('k') == false){
        window.stack.metadata['k'] = {} //for compatibility with projects saved before k added
    }


	//update the dropdown list of options
	var nwselect = $('#nwlist')
	var nwOpts = '<option selected="selected" value="">Select a network</option>'
	for(val in window.stack){
		if(val != 'metadata'){
			bit = '<option value ="' + val + '">' + val + '</option>'
			nwOpts += bit
		};
	};
		
	nwselect.html(nwOpts)

	//hack to get upload to bypass network selection dropdown the first time
	//will be removed when upload is separated from the viewer

	var nn = Object.keys(window.stack);
	nn.splice(nn.indexOf('metadata'),1)
	var autoselect = nn[0] 
	cy.load(JSON.parse(window.stack[autoselect])) //load and display the network
	cy.layout({name:'preset'})
	document.getElementById('nw_name').value = autoselect //set to name of current network
	window.currentNwName = autoselect;

	//apply the visual style classes
	update_style();

	//use something like this to also set the dropdown - need to guarantee it selects the right one, this doesn't fire an onchange
	//document.getElementById("nwlist").selectedIndex = 2;
	console.log('network loaded by autoselect hack')

	//printState();
};

//function to upload a petri net from Snoopy
//for testing
/*
var fileInput = document.getElementById('file_upload');
var file = fileInput.files[0];
var reader = new FileReader();
reader.readAsText(file)
uploadFromSnoopy(reader.result)
*/
function uploadFromSnoopy(uploaded){
	resetStack()
	console.log('upload from snoopy');
	var lines = uploaded.split(/\r\n|\n/);
	var projectName =  _.filter(lines,function(el){ return el.indexOf('pn.Name') >= 0})
	var pnames =  _.filter(lines,function(el){return el.indexOf('pn.P =') >= 0})
	var marking =  _.filter(lines,function(el){return el.indexOf('pn.m0') >= 0})
	var tnames =  _.filter(lines,function(el){return el.indexOf('pn.T =') >= 0})
	var k = _.filter(lines,function(el){return el.indexOf('MassAction') >= 0})

	//the matrices will span multiple lines
	var pre_start = _.filter(lines,function(el){return el.indexOf('pn.PreArcs =') >= 0})
	var pre = [];
	var go = true;
	var i = lines.indexOf(pre_start[0]) + 1;
	while(go == true){
		if(lines[i].indexOf('];') >= 0){
			go = false;
		}
		else {
			pre.push(lines[i])
			i++;
		}
	}


	var post_start = _.filter(lines,function(el){return el.indexOf('pn.PostArcs =') >= 0})
	var post = [];
	var go = true;
	var i = lines.indexOf(post_start[0]) + 1;
	while(go == true){
		if(lines[i].indexOf('];') >= 0){
			go = false;
		}
		else {
			post.push(lines[i])
			i++;
		}
	}
	var inhib_start = _.filter(lines,function(el){return el.indexOf('pn.InhibitorArcs =') >= 0})
	var inhib = [];
	var go = true;
	var i = lines.indexOf(inhib_start[0]) + 1;
	while(go == true){
		if(lines[i].indexOf('];') >= 0){
			go = false;
		}
		else {
			inhib.push(lines[i])
			i++;
		}
	}

	k.forEach(function(el,idx,arr){arr[idx]=parseFloat(el.split('(')[2].split(',')[0])})

	console.log('projectname: ',projectName)
	console.log('pnames: ',pnames)
	console.log('tnames: ',tnames)
	console.log('marking: ',marking)
	console.log('pre: ',pre)
	console.log('post: ',post)
	console.log('inhib: ', inhib)
	console.log('mass action: ',k)

	//strings to arrays
	var out = {}
	out['pre'] = matlabArrayToJs(pre)
	out['post'] = matlabArrayToJs(post)
	out['inhib'] = matlabArrayToJs(inhib)
	marking = marking[0].split('[')[1].split(']')
	marking.splice(marking.indexOf(';'),1)
	marking = marking[0].split(',')
	marking.forEach(function(el,idx,arr){arr[idx] = parseInt(el)})
	out['marking'] = marking

	//process place and transition names. In snoopy, names must begin with a letter and can only contain letters, numbers and underscores
	//but from matlab they will be in single quotes and have a preceeding space
	pnames = pnames[0].split('{')[1].split('}')[0].split(',')
	pnames.forEach(function(el,idx,arr){
		//global regular expression to replace single quote with nothing
		//word start anchored replacement of preceeding spaces
		arr[idx] = el.replace(/'/g,"").replace(/^ */,"")

	})
	out['pnames'] = pnames

	tnames = tnames[0].split('{')[1].split('}')[0].split(',')
	tnames.forEach(function(el,idx,arr){
		//global regular expression to replace single quote with nothing
		//word start anchored replacement of preceeding spaces
		arr[idx] = el.replace(/'/g,"").replace(/^ */,"") 
	})
	out['tnames'] = tnames
	
	out['k'] = k
	projectname = projectName[0].split('= ')[1].split(';')[0]
	projectname = projectname.replace(/'/g,"")
	out['projectname'] = projectname
	
	//return out

	var mat = out

	//view the network
	var nodes = []
	var idmap = {} //map from name to id
	//places
	for (var i = 0; i < mat.pnames.length; i++) {
		var default_name = "n" + window.stack.metadata['nodecounter'];
		nodes.push( {group: "nodes", data: { id: default_name , name: mat.pnames[i], marking: mat.marking[i] }, classes: "place" } )
		//update counter
		window.stack.metadata['nodecounter'] += 1;
		idmap[mat.pnames[i]] = default_name;

		//metadata
		window.stack.metadata.place[mat.pnames[i]] = mat.marking[i]
	};


	//transitions
	for (var i = 0; i < mat.tnames.length; i++) {
		var default_name = "n" + window.stack.metadata['nodecounter'];
		nodes.push( {group: "nodes", data: { id: default_name , name: mat.tnames[i], marking: 0}, classes: "transition" } )
		//update counter
		window.stack.metadata['nodecounter'] += 1;
		idmap[mat.tnames[i]] = default_name;

		//metadata
		window.stack.metadata.transition[mat.tnames[i]] = 0;
	};
	//create edges - transitions are rows, places are columns
	//normal
	//prearcs - from place to transition, postarcs - from transition to place
	//all arc matrices have the same shape so do together but need to change source and target between them
	var edges = [];
	for (var i = 0; i < mat.pre.length; i++) { //i is over places  (rows)
		for (var j = 0; j < mat.pre[i].length; j++) { //j is over transitions (columns)
			//prearc
			if(mat.pre[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.pnames[i]],
						target: idmap[mat.tnames[j]],
						multiplicity: mat.pre[i][j]
					},
					classes: 'normal'
				})  
				window.stack.metadata.edgecounter += 1;
			}
			//postarc
			if(mat.post[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.tnames[j]],
						target: idmap[mat.pnames[i]],
						multiplicity: mat.post[i][j]
					},
					classes: 'normal'
				})  
				window.stack.metadata.edgecounter += 1;
			}

			//inhibitor
			if(mat.inhib[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.pnames[i]],
						target: idmap[mat.tnames[j]],
						multiplicity: mat.inhib[i][j]
					},
					classes: 'inhibitor'
				})  
				window.stack.metadata.edgecounter += 1;
			}
		};
	};

	//metadata
	mat.k.forEach(function(el,idx,arr){
		if(el > 1){
			window.stack.metadata.k[mat.tnames[idx]] = el
		}
	})

	//display
	window.currentNwName = mat.projectname
	document.getElementById('nw_name').value = window.currentNwName;

	cy.load({
			nodes: nodes,
			edges: edges
		})

	cy.layout({name:'grid'})
	remove_style();

	//update the dropdown list of existing networks
	var nwselect = $('#nwlist')
	var nwOpts = '<option selected="selected" value="">Select a network</option>'
	for(val in window.stack){
		if(val != 'metadata'){
			bit = '<option value ="' + val + '">' + val + '</option>'
			nwOpts += bit
		};
	};
		
	nwselect.html(nwOpts)

}
 function testSnoopy(){
 	var fileInput = document.getElementById('file_upload');
	var file = fileInput.files[0];
	var reader = new FileReader()
	reader.readAsText(file)
	uploadFromSnoopy(reader.result)
 }

 function matlabArrayToJs(matlab){
 	//convert array from ["0,0,0,0;","1,1,1,1;"] to array of arrays
 	//don't need to remove the trailing ';' from the final element of each row as parseInt ignores it
 	var result = []
 	for (var i = 0; i < matlab.length; i++) {
 		var a = matlab[i].split(',')
 		a.forEach(function(el,idx,arr){arr[idx] = parseInt(el)})
 		result.push(a)
 	};
 	return result
 }

//CREATE A NEW NETWORK
//save the current network in the stack
function newnw(){
	//save the data for the currently loaded network
	updateStack();
	//update the list of networks and clear the window
	
	window.currentNwName = getDefaultNwName();
	//document.getElementById('nw_name').value = 'network_name' //reset to default
	document.getElementById('nw_name').value = window.currentNwName;
	
	//create a new blank view
	cy.load()
	cy.layout({name:'grid'}) //must be grid to allow node creation
	//printState()
	
	//update the dropdown list of existing networks
	var nwselect = $('#nwlist')
	var nwOpts = '<option selected="selected" value="">Select a network</option>'
	for(val in window.stack){
		if(val != 'metadata'){
			bit = '<option value ="' + val + '">' + val + '</option>'
			nwOpts += bit
		};
	};
		
	nwselect.html(nwOpts)
}

//function to nest a network within the currently selected node
function nestWithin(nodeName){
	//when the user clicks "nest within", we will automatically create a new network name it the same as the selected node
	//if you nest a network within a disperse place, all appearances will contain the same network

	//save the data for the currently loaded network
	updateStack();
	
	//update the list of networks and clear the window
	
	//DON'T PROMPT FOR A NAME
	//document.getElementById('nw_name').value = 'network_name' //reset to default
	window.currentNwName = nodeName;
	document.getElementById('nw_name').value = window.currentNwName;
	
	//create a new blank view
	cy.load()
	cy.layout({name:'grid'}) //must be grid to allow node creation
	//printState()
	
	//update the dropdown list of existing networks
	var nwselect = $('#nwlist')
	var nwOpts = '<option selected="selected" value="">Select a network</option>'
	for(val in window.stack){
		if(val != 'metadata'){
			bit = '<option value ="' + val + '">' + val + '</option>'
			nwOpts += bit
		};
	};
		
	nwselect.html(nwOpts)

	//clear the info panel
	clear();

	//remove k metadata
	if(window.stack.metadata.k.hasOwnProperty(nodeName)){
		delete window.stack.metadata.k[nodeName]
	}

};

//reload an existing network
$("select#nwlist").on('change',function(){ //rewrite to use same logic as newnw - add var to keep track of whether or not to do the swich
	//save the data for the currently loaded network
	updateStack();
	//load the pre-existing network that was selected
	load_from_stack($(this).val())

});

//function to load an existing network from the stack - used by the network list and also to load a different network after a network is deleted
function load_from_stack(nwname){
	//in case the user has started creating an edge then tries to swich networks
	cancel_edge() //otherwise source and target could be in different networks, which doesn't work

	//load the pre-existing network that was selected
		cy.load(JSON.parse(window.stack[nwname])) //load and display the network
		cy.layout({name:'preset'})
		document.getElementById('nw_name').value = nwname; //set to name of current network
		window.currentNwName = nwname;
		console.log('network loaded')
		
		//apply visual styles
		update_style();
		
		//update the info panel
		//printState();
		
		//update the dropdown list of existing networks
		var nwselect = $('#nwlist')
		var nwOpts = '<option selected="selected" value="">Select a network</option>'
		for(val in window.stack){
			if(val != 'metadata'){
				bit = '<option value ="' + val + '">' + val + '</option>'
				nwOpts += bit
			};
		};
			
		nwselect.html(nwOpts)
}

//function to load a network by name
function goTo(network){
	//save the data for the currently loaded network
	updateStack();
	load_from_stack(network);
	
};

///////////
//function to update visual style when a new network is loaded
function update_style(){
	var coarse_p = Object.keys(window.stack.metadata.contains);
	var disperse = Object.keys(window.stack.metadata.disperse);
	var coarse_t = Object.keys(window.stack);
	
	for(var i=0; i<coarse_p.length; i++){
		var toupdate = cy.filter("node[name = '" + coarse_p[i] +"']");
		toupdate.addClass('contains');
	};
	for(var i=0; i<disperse.length; i++){
		var toupdate = cy.filter("node[name = '" + disperse[i] +"']");
		toupdate.addClass('disperse');
	};
	for(var i=0; i<coarse_t.length; i++){
		var toupdate = cy.filter("node[name = '" + coarse_t[i] +"']");
		toupdate.addClass('coarsetransition');
	};
};

//function to remove all classes related to visual style
function remove_style(){
	var toupdate = cy.filter(function(i, element){
				if( element.isNode() && element.hasClass('contains') ){
					return true;
					}
					return false;
				});	
	toupdate.removeClass('contains');
	
	var toupdate = cy.filter(function(i, element){
				if( element.isNode() && element.hasClass('disperse') ){
					return true;
					}
					return false;
				});	
	toupdate.removeClass('disperse');
	
	var toupdate = cy.filter(function(i, element){
				if( element.isNode() && element.hasClass('coarsetransition') ){
					return true;
					}
					return false;
				});	
	toupdate.removeClass('coarsetransition');
	
				
};

//sandbox new UI prompt
function check_changes_modal(){
bootbox.dialog({
  message: "There are unsaved changes to the current network",
  title: "Save changes?",
  closeButton: false,
  animate: false,
  buttons: {
    success: {
      label: "Save changes",
      className: "btn-success",
      callback: function() {
        console.log('changes saved')
      }
    },
    danger: {
      label: "Discard changes",
      className: "btn-danger",
      callback: function() {
        console.log('changes discarded')
      }
    },
    main: {
      label: "Save as",
      className: "btn-primary",
      callback: function() {
        console.log('save under new name')
      }
	},
	cancel: {
		label: "Cancel action",
		className: "btn-cancel",
		callback: function() {
			console.log('action was cancelled')
		}
    }
  }
});
}

//EDITOR FUNCTIONS
function cancel_edge(){
	//cancel edge creation after a source node has been clicked
	window.sourcenode = -1
	clear(); //remove any text from the display area
};

//CREATE MATRIX FOR SIMULATION
//places in columns, transitions in rows
//all multiplicities are positive
//marking, prearcs, postarcs, inhibitors
//use node id to place edges in the matrix as it is immutable. map id to name at the end
/*
//list places
var n = cy.nodes();
for(var i = 0; i<n.length; i++){if(n[i].hasClass('place')){console.log('place ' + n[i].id() + n[i].data('name'))}};

//find edge with source n0
var e = cy.edges()
for(var i=0; i<e.length; i++){if(e[i].data('source')=='n0'){console.log('edge from ' + e[i].data('source') + ' to ' + e[i].data('target'))}}
*/
function export_matrix(){
	//set up arrays
	var nodes = cy.nodes();
	var edges = cy.edges();
	var out = make_matrix(nodes,edges);
	out_json = JSON.stringify(out);
	//$('#output_area').append(out_json);
	var blob = new Blob([out_json], {type: "text/plain;charset=utf-8"});
	//saveAs(blob, document.getElementById('set_filename').value + '.txt');	
	saveAs(blob, 'generated_matrices' + '.txt');
};

function make_matrix(nodes,edges){
	var p_ids = [];
	var t_ids = [];
	var p_names = [];
	var t_names = [];
	var marking = [];
	var pre = []; //pre, post and inhib will be set to [TxP] array of 0's below
	var post = [];
	var inhib = [];
	
	
	//get a list of all places and transitions, fill in marking matrix
	for(var i=0; i<nodes.length; i++){
		var id = nodes[i].id();
		var name = nodes[i].data('name');
		if(nodes[i].hasClass('place')){
			p_ids.push(id);
			p_names.push(name);
			marking.push(nodes[i].data('marking'));	
		} else {
			t_ids.push(id);	
			t_names.push(name);
		}
	}
	
	//iterate through edges, fill in the pre, post and inhib matrices
	//pre is place->transition, post is transition->place. prearcs can be inhibitor or normal arcs, postarcs can only be normal
	//init as all 0
	var zeroes = [];
	for(var i = 0; i<p_ids.length; i++){
		zeroes.push(0);
	}
	for(var i=0; i<t_ids.length; i++){
		pre.push(zeroes.slice(0)); //can use slice for a shallow copy
		post.push(zeroes.slice(0));
		inhib.push(zeroes.slice(0));
	}
	
	//iterate through edeges
	for(var i=0; i<edges.length;i++){
		var e = edges[i];
		var src = e.data('source')
		var tgt = e.data('target')
		if(t_ids.indexOf(src) >=0){
		 	//the edge source is a transition
			//has to be a postarc
			//get index of source and target
			srcidx = t_ids.indexOf(src);
			tgtidx = p_ids.indexOf(tgt);
			post[srcidx][tgtidx] = e.data('multiplicity')
				
		} else {
			//the edge source is a place
			srcidx = p_ids.indexOf(src);
			tgtidx = t_ids.indexOf(tgt);
			//could be a normal or an inhibitor edge
			if(e.hasClass('inhibitor')){
				//inhibitor edge
				inhib[tgtidx][srcidx] = e.data('multiplicity')
			} else {
				//normal edge
				pre[tgtidx][srcidx] = e.data('multiplicity')
			};
		}; 
	}
	
	
	//output
	/*
	$('#output_area').html('<p> places: ' + p_ids + '</p><p>transitions: ' + t_ids + '</p>');
	printmatrix(pre,'pre',t_ids)
	printmatrix(post,'post',t_ids)
	printmatrix(inhib,'inhib',t_ids)
	
	$('#output_area').append('<p> marking: ' + marking + '</p>')
	*/
	//make a json object for output
	var out = {};
	out['pnames'] = p_names;
	out['tnames'] = t_names;
	out['pre'] = pre;
	out['post'] = post;
	out['inhib'] = inhib;
	out['marking'] = marking;
	
	return out;
};


function printmatrix(m,name,transitions){
	//only used for debugging
	//transitions is t_ids from the export_matrix() function
	$('#output_area').append('<p>' +  name +': ');
	for(var i = 0; i<transitions.length; i++){
		$('#output_area').append('<br>' + m[i] )	
	}
	$('#output_area').append('</p>');
};

function megamerge(){
	
	var out = merge();
	
	out_json = JSON.stringify(out.matrices);
	out_json = out_json + '\n'
	//$('#output_area').append(out_json);
	var blob = new Blob([out_json], {type: "text/plain;charset=utf-8"});
	//saveAs(blob, document.getElementById('set_filename').value + '.txt');	
	saveAs(blob, 'merge_matrices' + '.txt');
	
};

function mergeAndView(){
	var mat = merge(); //merge all networks and return the result
	cy.load() //clear the viewer
	viewFromMatrix(mat.matrices, mat.namemap)

}

//construct network from a matrix format
//used to view merge result and to view upload from snoopy
function viewFromMatrix(mat,namemap){
	//create nodes
	var nodes = []
	var idmap = {} //map from name to id
	//places
	for (var i = 0; i < mat.pnames.length; i++) {
		var default_name = "n" + window.stack.metadata['nodecounter'];
		nodes.push( {group: "nodes", data: { id: default_name , name: mat.pnames[i], marking: mat.marking[i] }, classes: "place" } )
		//update counter
		window.stack.metadata['nodecounter'] += 1;
		idmap[mat.pnames[i]] = default_name;
	};


	//transitions
	for (var i = 0; i < mat.tnames.length; i++) {
		//var default_name = "n" + window.stack.metadata['nodecounter'];
		var default_name = namemap[mat.tnames[i]][0] //use the same id as in the other network so the citations are linked. This works because transition nodes must be unique
		nodes.push( {group: "nodes", data: { id: default_name , name: mat.tnames[i], marking: 0}, classes: "transition" } )
		//update counter
		window.stack.metadata['nodecounter'] += 1;
		idmap[mat.tnames[i]] = default_name; //map to use for making edges
	};
	//create edges - transitions are rows, places are columns
	//normal
	//prearcs - from place to transition, postarcs - from transition to place
	//all arc matrices have the same shape so do together but need to change source and target between them
	var edges = [];
	for (var i = 0; i < mat.pre.length; i++) { //j is over transitions (rows)
		for (var j = 0; j < mat.pre[i].length; j++) { //i is over places (columns)
			//prearc
			if(mat.pre[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.pnames[j]],
						target: idmap[mat.tnames[i]],
						multiplicity: mat.pre[i][j]
					},
					classes: 'normal'
				})  
				window.stack.metadata.edgecounter += 1;
			}
			//postarc
			if(mat.post[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.tnames[i]],
						target: idmap[mat.pnames[j]],
						multiplicity: mat.post[i][j]
					},
					classes: 'normal'
				})  
				window.stack.metadata.edgecounter += 1;
			}

			//inhibitor
			if(mat.inhib[i][j] != 0){
				var setID = window.stack.metadata.edgecounter.toString();
				edges.push({
					group: "edges",
					data: {
						id: "e" + setID,
						source: idmap[mat.pnames[j]],
						target: idmap[mat.tnames[i]],
						multiplicity: mat.inhib[i][j]
					},
					classes: 'inhibitor'
				})  
				window.stack.metadata.edgecounter += 1;
			}
		};
	};

	//display
	window.currentNwName = "Merge-result"
	document.getElementById('nw_name').value = window.currentNwName;

	cy.load({
			nodes: nodes,
			edges: edges
		})

	cy.layout({name:'grid'})
	remove_style();

	//update the dropdown list of existing networks
	var nwselect = $('#nwlist')
	var nwOpts = '<option selected="selected" value="">Select a network</option>'
	for(val in window.stack){
		if(val != 'metadata'){
			bit = '<option value ="' + val + '">' + val + '</option>'
			nwOpts += bit
		};
	};
		
	nwselect.html(nwOpts)
}

function merge(){
	/*
	merge all networks and RETURN the result
	need to use NAME for edges not ID
	*/
	//check if there is anything in the current network, if so then add it to the stack
	var jdata = cy.json()['elements'];
	if('nodes' in jdata){
		//alert('overwriting data for currently open network')
		window.stack[window.currentNwName] = JSON.stringify(jdata)
	};
	//generate the real network from a network with coarse transitions
	//have to map id back to name to place edges when merging - IDs will all be unique even if the name is the same
	//ID -> NAME will be many -> one
	console.log('start megamerge')
	//go through each graph in the stack, create a list of all nodes and edges
	var nodes = [];
	var edges = [];
	
	console.log('get all nodes and edges')
	var allNwNames = Object.keys(window.stack)
	allNwNames.splice(allNwNames.indexOf('metadata'),1) //remove metadata from the list of networks
	if(allNwNames.indexOf('Merge-result') >= 0){
		allNwNames.splice(allNwNames.indexOf('Merge-result'),1) //remove merge network from the list of networks
	}
	for(var i=0; i<allNwNames.length; i++){
		var tmp = JSON.parse(window.stack[allNwNames[i]]); //load network data - doesn't get displayed
		
		if('nodes' in tmp){ //if the network is empty calling Object.keys gives an error
			var tmp_n = Object.keys(tmp.nodes)
			for(var j=0; j<tmp_n.length;j++){ //needed to build the idmap later
				nodes.push(tmp.nodes[tmp_n[j]]);
			};
			//check that the network contains edges
			if(tmp.hasOwnProperty('edges')){
				var tmp_e = Object.keys(tmp.edges)
				for(var j=0; j<tmp_e.length;j++){
					edges.push(tmp.edges[tmp_e[j]]);
				};	
			};
		}
	};
	
	
	//make the matrix - can't use make_matrix() as that requires cytoscape to load the network first
	var p_ids = [];
	var t_ids = [];
	var p_names = [];
	var t_names = [];
	var marking = [];
	var kvector = [];
	var pre = []; //pre, post and inhib will be set to [TxP] array of 0's below
	var post = [];
	var inhib = [];
	var idmap = {}; //used to map node ID's to names
	var namemap = {}; //used to make node name to IDs
	var unique_p = Object.keys(window.stack.metadata.place);
	var unique_t = Object.keys(window.stack.metadata.transition);
	
	console.log('sort nodes into places and transitions')
	//get a list of all places and transitions
	for(var i=0; i<nodes.length; i++){
		var id = nodes[i].data['id'];
		var name = nodes[i].data['name'];
		if(nodes[i].classes.search('place') >= 0){ //places can now have multiple classes, which will all be one string so use the string .search() method, returns -1 if no match found
			p_ids.push(id);
			p_names.push(name);
			idmap[id] = name;
			if(name in namemap){
				namemap[name].push(id);
			} else {
				namemap[name] = [id];
			};
		} else {
			t_ids.push(id);	
			t_names.push(name);
			idmap[id] = name;
			
			//used when rebuilding the network for the viewer to link transition nodes back to citation data
			if(name in namemap){
				namemap[name].push(id);
			} else {
				namemap[name] = [id];
			}
		}
	}
	//console.log(idmap);
	console.log('namemap:');
	console.log(namemap);
	
	console.log('delete coarse transition nodes and their edges')
	//delete coarse transition nodes and their edges
	var edgemap = makeEdgeMap(edges) //to make the edges searchable
	for(var i=0; i<t_ids.length; i++){ //t_names and t_ids refer to the same node at the same index
		//it is a coarse transition if the name == the name of a network
		if(allNwNames.indexOf(t_names[i]) >= 0){ // no two networks can have the same name so indexOf is ok, there can only up to 1 match
			//delete its edges from edges
			var del = [];
			if(edgemap.source.indexOf(t_ids[i]) >= 0){ //will be -1 if no occurrences
				var idx = findAll(edgemap.source, t_ids[i])
				del = idx
				/* this code was included here but we don't need to know anythign about the edges we remove in this case
				the code is also broken because the edg_src doesn't exist yet. If nothing breaks by removing it 
				then delete forever*/
				//use filter to get a new array of the edges where this node is the target
				//probably don't need .concat in this case?
				//edg_src = edg_src.concat( edges.filter(function(el){return el.data.source.indexOf(t_ids[j])>=0}) )
				//this: var edg_src = [edges[idx]] doesn't work because there can be MULTIPLE edges	
			};
			if(edgemap.target.indexOf(t_ids[i]) >= 0){
				var idx = findAll(edgemap.target, t_ids[i])
				//edg_tgt = edg_tgt.concat( edges.filter(function(el){return el.data.target.indexOf(t_ids[j])>=0}) )
				//var edg_tgt = [edges[idx]] see above
				if(del.length > 0) { //if some edges were already found
					del = del.concat(idx)
				} else {
					del = idx
				};
			};
			//build a new array with the items to be kept - if we delete iteratively then the index from del will become wrong after the first loop
			//could also loop in reverse order, starting with the highest value in del
			/* i.e. this is wrong
			for(var j=0; j<del.length; j++){
				edges.splice(del[j],1)	
			}
			*/ 
			
			var new_edges = [];
			for(var j=0; j<edges.length; j++){
				if(del.indexOf(j) < 0){ //if the index is not marked for removal, push it to the new array
					new_edges.push(edges[j])	
				};	
			};
			
			//copy the new list of edges into 'edges', update the searchable edgemap
			edges = new_edges;
			var edgemap = makeEdgeMap(edges)
			
			//delete the transition from unique_t - doesn't matter if it still exists in the other lists because it won't have any edges mapping to it
			unique_t.splice(unique_t.indexOf(t_names[i]),1)
			
			
		};
	};
	
	
	
	console.log('handle coarse places')
	//handle coarse places
	var queue = Object.keys(window.stack.metadata.contains); //all coarse places
	//work out the top level based on the nodes that contain other nodes but are not themselves contained by any other node
	//have to use a different method inside the loop but this works the first time
	var toplevel = []; //top level of hierarchy of unprocessed nodes
	var lowerlevel = []; //places still to be processed in the next iteration
	for(var i=0; i<queue.length; i++){
		if(!(queue[i] in window.stack.metadata.isa)){
			console.log(queue[i] + 'is a top level coarse place')
			toplevel.push(queue[i])
			
		} else {
			lowerlevel.push(queue[0])	
		};
	};
	
	while(toplevel.length > 0){
		//need to rebuild the edge map every time as edges are added and deleted in the loop
		var edgemap = makeEdgeMap(edges); //this makes the edges searchable by node id without loading the data into cytoscape
		//init newtop as empty array, will fill in during the loop. If it stays empty then the loop will exit because we reached the lowest level in the hierarchy
		var newtop = [] //places contained by the current top level places will be the top level in the next iteration
		
		//process top level places
		for(var i=0; i<toplevel.length; i++){
			//edges where the coarse place is the source
			var edg_src = [];
			var edg_tgt = [];
			var del = [];
			
			//get the edges for all appearances of this place
			//get all the ids that refer to the current toplevel place - this way a disperse place can be coarse
			var ids = namemap[toplevel[i]]
			
			for(var j = 0; j < ids.length; j++){
				//for every ID that refers to this top level place, get all the edges
				if(edgemap.source.indexOf(ids[j]) >= 0){ //will be -1 if no occurrences
					var idx = findAll(edgemap.source, ids[j])
					edg_src = edg_src.concat( edges.filter(function(el){return el.data.source == ids[j]}) )
					del = del.concat(idx);
				};
				if(edgemap.target.indexOf(ids[j]) >= 0){
					var idx = findAll(edgemap.target, ids[j])
					edg_tgt = edg_tgt.concat( edges.filter(function(el){return el.data.target == ids[j]}) )
					del = del.concat(idx);
					
				};		
			};
			
			//what place NAMES are contained by this place
			var subp = window.stack.metadata.contains[toplevel[i]] //these will be NAMES
			newtop = newtop.concat(subp) //add the names to the list to be used as the next top level
			
			//only add the edge to ONE appearance of each sub place - the edges of disperse places get pooled later
			var subp_id = [];
			for(var j = 0; j< subp.length; j++){
				var tmp = namemap[subp[j]]
				subp_id[j] = tmp[0] //only the first appearance
			};
			
			for(var j=0; j<edg_src.length; j++){
				for(var sp in subp_id){
					//make a new edge for each sub place	
					var tmp = cloneEdge(edg_src[j]); //duplicate the edge
					tmp.data['source'] = subp_id[sp]; //replace the coarse place with each place it contains
					edges.push(tmp)
					console.log('created edge from: ' + tmp.data.source + " to " + tmp.data.target)
				};
			};
			
			//repeat for edges where the coarse place is the target
			for(var j=0; j<edg_tgt.length; j++){
				for(var sp in subp_id){
					//make a new edge for each sub place	
					var tmp = cloneEdge(edg_tgt[j]); //duplicate the edge
					tmp.data['target'] = subp_id[sp]; //replace the coarse place with each place it contains
					edges.push(tmp)
					console.log('created edge2 from: ' + tmp.data.source + " to " + tmp.data.target)
				};
			};
			
			//delete all the edges for the coarse place - build a new array ignoring those indices that are marked for removal
			var new_edges = [];
			for(var j=0; j<edges.length; j++){
				if(del.indexOf(j) < 0){ //if the index is not marked for removal, push it to the new array
					new_edges.push(edges[j])	
				};	
			};
			edges = new_edges; //edgemap will be updated at the start of the next iteration
			
			//delete the place from unique_p
			unique_p.splice(unique_p.indexOf(toplevel[i]),1)	
			
		};
		
		//update the queue
		//every place that was contained by the previous top level is now at the top level
		var toplevel = [];
		for(var i = 0; i<newtop.length; i++){
			//remove any places from the top level that don't contain anything - i.e. we have reached the bottom for that branch of the hierarchy	
			if(newtop[i] in window.stack.metadata.contains){
				//only places that actually contain other places should be included
				toplevel.push(newtop[i])
			};
		};
		console.log(toplevel)
	};
	
	
	console.log('fill in matrices')
	
	//fill in marking matrix
	for(var i=0; i<unique_p.length; i++){
		marking.push(window.stack.metadata.place[unique_p[i]]);
	};
	
	//fill in paramater k vector
	for(var i=0; i<unique_t.length; i++){
		if(window.stack.metadata.k.hasOwnProperty(unique_t[i])){
			kvector.push(window.stack.metadata.k[unique_t[i]]);
		} else {
			kvector.push(1);
		}
		
	};	
	
	//iterate through edges, fill in the pre, post and inhib matrices
	//pre is place->transition, post is transition->place. prearcs can be inhibitor or normal arcs, postarcs can only be normal
	//init as all 0
	var zeroes = [];
	for(var i = 0; i<unique_p.length; i++){
		zeroes.push(0);
	}
	for(var i=0; i<unique_t.length; i++){
		pre.push(zeroes.slice(0)); //can use slice for a shallow copy
		post.push(zeroes.slice(0));
		inhib.push(zeroes.slice(0));
	}
	
	//iterate through edeges
	console.log(edges);
	console.log(idmap);
	for(var i=0; i<edges.length;i++){
		console.log(i)
		var e = edges[i];
		var src = e.data['source'];
		src = idmap[src]; //convert ID to a NAME
		var tgt = e.data['target'];
		tgt = idmap[tgt];

		console.log('update matrix for edge source:' + src + ' target ' + tgt)
		
		if(unique_t.indexOf(src) >=0){
		 	//the edge source is a transition
			//has to be a postarc
			//get index of source and target
			srcidx = unique_t.indexOf(src);
			tgtidx = unique_p.indexOf(tgt);
			post[srcidx][tgtidx] = e.data['multiplicity']
				
		} else {
			//the edge source is a place
			srcidx = unique_p.indexOf(src);
			tgtidx = unique_t.indexOf(tgt);
			//could be a normal or an inhibitor edge
			if(e.classes == 'inhibitor'){
				//inhibitor edge
				inhib[tgtidx][srcidx] = e.data['multiplicity'];
			} else {
				//normal edge
				pre[tgtidx][srcidx] = e.data['multiplicity'];
			};
		}; 
	}
	
	
	//output
	/*
	$('#output_area').html('<p> places: ' + p_ids + '</p><p>transitions: ' + t_ids + '</p>');
	printmatrix(pre,'pre',t_ids)
	printmatrix(post,'post',t_ids)
	printmatrix(inhib,'inhib',t_ids)
	
	$('#output_area').append('<p> marking: ' + marking + '</p>')
	*/
	//make a json object for output
	var out = {};
	out['pnames'] = unique_p;
	out['tnames'] = unique_t;
	out['pre'] = pre;
	out['post'] = post;
	out['inhib'] = inhib;
	out['marking'] = marking;
	out['k'] = kvector;
	
	var ret = {matrices:out, namemap: namemap}

	return ret;
}

function makeEdgeMap(edges){
	//from the array of all edges in the network, make an object containing two arrays - the source and target nodes for each edge
	//this makes the edges searchable by node id
	var edgemap = {};
	edgemap['source'] = [];
	edgemap['target'] = [];
	for(var i=0; i<edges.length; i++){
		edgemap.source.push(edges[i].data['source'])
		edgemap.target.push(edges[i].data['target'])
	};
	
	return edgemap
};

function cloneEdge(e){
	//copy an edge for use when merging networks and expanding coarse places
	//only copies the attributes needed to build the matrix, it is not a full cytoscape edge object (but contains enough info to make one with user-defined properties conserved)
	var copy = {};
	copy['data'] = {};
	copy['classes'] = e.classes;
	for(var attr in e.data){
		copy.data[attr] = e.data[attr]
	};
	return copy
};

function findAll(array,thing){
	var indices = [];
	var idx = array.indexOf(thing);
	while (idx != -1) {
    	indices.push(idx);
    	idx = array.indexOf(thing, idx + 1); //second argument is where to start searching from
	};
	return indices
};

//functions to  make sure the entered name is ok
function getNwName(){
	var entered_name = prompt('Name the new network:');            
	
	var okname = nameOK(entered_name);
	while(!okname){
		var entered_name = prompt('Name the new network:');
		var okname = nameOK(entered_name);
	};
	window.currentNwName = entered_name
	
};

function nameOK(name){
	if(name == null || name == ""){
		alert('you must enter a name')
		return false	
	} else if(name in window.stack){
		alert('The name ' + name + 'is already in use')
		return false	
	} else if (name == "Merge-result"){
		alert('The name "Merge-result" is reserved for use by the application.')
	} else {
		return true
	};
};

/////////////////////// export as csv
//function to download network data as a two-column csv file for import into cytoscape
//format: source, source type, edge type, target, target type \r\n
function exportToTC(what,nwname){
	//what can be 'all','merge', 'one', 'current' to export all networks, merge then export, the current network or a specified network
	//nwname must be a network name if what == 'one'

	//update stack for current network
	var jdata = cy.json()['elements'];
	if('nodes' in jdata){
		//alert('overwriting data for currently open network')
		window.stack[window.currentNwName] = JSON.stringify(jdata)
	};

	var edges = [];
	var nodes = [];
	var idmap = {};
	var typemap = {}; //maps node id to node type
	if(what == 'all'){
		
		var allNwNames = Object.keys(window.stack)
		allNwNames.splice(allNwNames.indexOf('metadata'),1) //remove metadata from the list of networks
		if(allNwNames.indexOf('Merge-result') >= 0){
			allNwNames.splice(allNwNames.indexOf('Merge-result'),1) //remove merge network from the list of networks
		}
		for(var i=0; i<allNwNames.length; i++){
			var tmp = JSON.parse(window.stack[allNwNames[i]]); //load network data - doesn't get displayed
			
			if('edges' in tmp){ //if the network is empty calling Object.keys gives an error
				edges = edges.concat(tmp.edges);
				nodes = nodes.concat(tmp.nodes);
			}
		}
	} else if(what == 'merge'){

		var mat = merge();
		mat = mat.matrices; //discard the util data needed elsewhere
		//now we have to convert the matrix into a graph again - slightly adjusted from mergeAndView to use an internal node and edge counter rather than the window counter

		var idmap = {} //map from name to id
		var internalNodeCounter = 0;
		var internalEdgeCounter = 0;
		//places
		for (var i = 0; i < mat.pnames.length; i++) {
			var default_name = "n" + internalNodeCounter;
			nodes.push( {group: "nodes", data: { id: default_name , name: mat.pnames[i], marking: mat.marking[i] }, classes: "place" } )
			//update counter
			internalNodeCounter += 1;
			idmap[mat.pnames[i]] = default_name;
		};


		//transitions
		for (var i = 0; i < mat.tnames.length; i++) {
			var default_name = "n" + internalNodeCounter;
			nodes.push( {group: "nodes", data: { id: default_name , name: mat.tnames[i], marking: 0}, classes: "transition" } )
			//update counter
			internalNodeCounter += 1;
			idmap[mat.tnames[i]] = default_name;
		};
		//create edges - transitions are rows, places are columns
		//normal
		//prearcs - from place to transition, postarcs - from transition to place
		//all arc matrices have the same shape so do together but need to change source and target between them
		for (var i = 0; i < mat.pre.length; i++) { //j is over transitions (rows)
			for (var j = 0; j < mat.pre[i].length; j++) { //i is over places (columns)
				//prearc
				if(mat.pre[i][j] != 0){
					var setID = internalEdgeCounter.toString();
					edges.push({
						group: "edges",
						data: {
							id: "e" + setID,
							source: idmap[mat.pnames[j]],
							target: idmap[mat.tnames[i]],
							multiplicity: mat.pre[i][j]
						},
						classes: 'normal'
					})  
					internalEdgeCounter += 1;
				}
				//postarc
				if(mat.post[i][j] != 0){
					var setID = internalEdgeCounter.toString();
					edges.push({
						group: "edges",
						data: {
							id: "e" + setID,
							source: idmap[mat.tnames[i]],
							target: idmap[mat.pnames[j]],
							multiplicity: mat.post[i][j]
						},
						classes: 'normal'
					})  
					internalEdgeCounter += 1;
				}

				//inhibitor
				if(mat.inhib[i][j] != 0){
					var setID = internalEdgeCounter.toString();
					edges.push({
						group: "edges",
						data: {
							id: "e" + setID,
							source: idmap[mat.pnames[j]],
							target: idmap[mat.tnames[i]],
							multiplicity: mat.inhib[i][j]
						},
						classes: 'inhibitor'
					})  
					internalEdgeCounter += 1;
				}
			};
		};
		

	} else {
		if(what == 'current'){
			var nwname = window.currentNwName;
		}
		var nwdata = JSON.parse(window.stack[nwname]);
		edges = edges.concat(nwdata.edges);
		nodes = nodes.concat(nwdata.nodes);
	}
	
	//create a mapping from id to name - edges are stored between ids
	//also create a mapping from node id to node type
	for (var i = 0; i < nodes.length; i++) {
		idmap[nodes[i].data.id] = nodes[i].data.name; 
		typemap[nodes[i].data.id] = nodes[i].classes;
	};
	

	var rows = '"source","source type","edge type","target","target type",\r\n';
	for (var i = 0; i < edges.length; i++) {
		var type = edges[i].classes.split(' ');
		type = type[type.length - 1];
		rows += '"' + idmap[edges[i].data.source] + '","'+ typemap[edges[i].data.source] + '","' + type + '","' + idmap[edges[i].data.target] + '","' + typemap[edges[i].data.target] + '" \r\n';
	};

	var blob = new Blob([rows], {type: "text/plain;charset=utf-8"});
	saveAs(blob, 'generated_csv.csv');
}

//export citations as csv
function exportCitations () {
	//export all citations in the format "transition name" , "[citations]" \r\n
	var rows = '"transition","citations",\r\n';

	//need to get element name so iterate over networks
	updateStack();

	nws = Object.keys(window.stack);
	nws.splice(nws.indexOf('metadata'),1) //remove metadata from the list of networks
	var idtoname = {} //map id to name for export
	for (var i = 0; i < nws.length; i++) {
		var nodes = JSON.parse(window.stack[nws[i]]).nodes
		nodes.forEach(function (el) {
			if(idtoname.hasOwnProperty(el.data.id)){
				console.log("Error: duplicate id: ",el.data.id)
			}
			idtoname[el.data.id] = el.data.name;
		})
	};

	var hascitation = Object.keys(window.stack.metadata.citations)
	hascitation.forEach(function(el){
		var c = window.stack.metadata.citations[el].join(', ');
		rows += '"' + idtoname[el] + '","' + c + '" \r\n';
	})
	var blob = new Blob([rows], {type: "text/plain;charset=utf-8"});
	saveAs(blob, 'exported_citations.csv');
}


////////////////////// network-level modifications
//rename a network
function rename_network(){
	//for now only allow the currently loaded network to be changed
	if(window.currentNwName in window.stack.metadata.transition){
		alert('You are renaming a network that is contained by a node - to maintain this relationship you must also rename the transition ' + window.currentNwName)
	}
	//check that any changes should be saved
	updateStack();
	
	if(window.currentNwName == 'Merge-result' && Object.keys(window.stack).length > 2){
		//the merge result exists as part of a project stack (rather than as an exported network from another project) so can't be edited
		alert('Merge results cannot be edited within the same project. To edit, save this network as a new project.')
	} else {	

		bootbox.prompt("Enter the new name for this network:", function(newName){
			if(nameOK(newName)){
				//copy the data under the current name into a new object with the new name
				window.stack[newName] = window.stack[window.currentNwName];
		
				//delete the old name from the stack
				delete window.stack[window.currentNwName];
		
				//track down any disperse node metadata containing the old name and update the metadata
				var disp = Object.keys(window.stack.metadata.disperse);
				for(var i=0; i<disp.length; i++){
					var foundInNw = Object.keys(window.stack.metadata.disperse[disp[i]]);
					//if the network we're renaming is one of the networks that this disperse place is found in, update the metadata
					var w = foundInNw.indexOf(window.currentNwName);
					if(w >= 0){
						window.stack.metadata.disperse[disp[i]][newName] = window.stack.metadata.disperse[disp[i]][window.currentNwName]
						delete window.stack.metadata.disperse[disp[i]][window.currentNwName];
					};
				}
				
				//update currentNwName
				window.currentNwName = newName;
				document.getElementById('nw_name').value = newName;
				
				//update nwlist dropdown
				var nwselect = $('#nwlist')
				var nwOpts = '<option selected="selected" value="">Select a network</option>'
				for(val in window.stack){
					if(val != 'metadata'){
						bit = '<option value ="' + val + '">' + val + '</option>'
						nwOpts += bit
					};
				};
					
				nwselect.html(nwOpts)
				
				//update visual styles - for some reason they disappear
				remove_style();
				update_style();
			}
			
		});
				
	}
	
};


//delete a network - for now only allow the currently selected network to be deleted, so I can use the existing remove_element function and just select all beforehand
function remove_network(){
	//the network to be removed is the currently loaded one
	var sure = confirm('Are you sure you want to delete this network? This action cannot be undone!')
	if(sure == true){
		//select all
		var all = cy.filter()
		all.select()
		
		//delete all the elements, updating metadata accordingly
		remove_element() //this will delete all currently selected nodes
		
		//now delete the current network from the stack
		delete window.stack[window.currentNwName];
		
		//now load a different network
		var nn = Object.keys(window.stack);
		nn.splice(nn.indexOf('metadata'),1)
		var autoselect = nn[0] 
		load_from_stack(autoselect);
	};
};

///////////////////// save and load from the server
function save_to_server(ignore_pid){ //if ignore_pid == 'ignore', it will save to a new session even if the project id has been set
	ignore_pid = typeof ignore_pid !== 'undefined' ? ignore_pid : 'continue';
	//window['esynOpts'] =  {projectid: '-1'};
	updateStack();

	//if the current network is empty and there is nothing else in the stack, prevent saving
	var allNwNames = Object.keys(window.stack)
	allNwNames.splice(allNwNames.indexOf('metadata'),1);
	var dosave = true;
	if(allNwNames.length == 0){
		dosave = false;
	}

	if(dosave){
		if($('#set_filename_form').length != 0){ //if the project id is not -1 we won't be able to edit the name
			var labeltxt = document.getElementById('set_filename').value;
		} else {
			var currentdate = new Date(); 
			var labeltxt = "Saved project: " + currentdate.getDate() + "/"
            + (currentdate.getMonth()+1)  + "/" 
            + currentdate.getFullYear() + " @ "  
            + currentdate.getHours() + ":"  
            + currentdate.getMinutes() + ":" 
            + currentdate.getSeconds();
		}

		labeltxt = encodeURIComponent(labeltxt);

		window.stack.metadata = JSON.stringify(window.stack.metadata); //we always need to stringify the metadata before saving
		if(window.esynOpts.projectid == '-1' || ignore_pid == 'ignore'){ //we haven't been given an ID yet or we are saving into a new project
			//Ajax request
			console.log('saving: ' + JSON.stringify(window.stack));
			if(ignore_pid == 'ignore'){
				labeltxt += ' (copy)';
			}
			
			$.ajax({ url: '../manager.php',
				 data: {action: 'XXXX', label: labeltxt, data: JSON.stringify(window.stack), type: window.esynOpts.type },
				 type: 'post',
				 dataType: 'text',
				 success: function(response){
					 console.log('the response :')
					 console.log(response)
					 console.log('end of response')
					 response = JSON.parse(response)
					 if(response['success'] == true){
					 	window.esynOpts.projectid = response['projectid']; //get the project id we have been assigned
					 	console.log('now working on session id: ' + window.esynOpts.projectid)
						if(ignore_pid == "continue"){
							alert('Saved successfully.')
						} else {
							//if we saved a copy, make it clear that we are still working on the original
							if(window.esynOpts.publishedid != -1){
								alert('Copy saved successfully, go to "My esyN" to edit it')
							} else {
								alert('Copy saved successfully, you are now working on the copy.')
								//change the project name text box
								var old = document.getElementById('set_filename').value;
								document.getElementById('set_filename').value = old + ' (copy)'
							}
						}
					 } else {
					 	alert('Save failed. You may not have permission to edit this project, but you can save your work as your own copy ("Save > Save a copy"). \n Error message: ' + response['message'])
					 }
				 }
				 })
			 
			 
		} else {
			//saving a new version of an existing session
			pid = window.esynOpts.projectid;
			$.ajax({ url: '../manager.php',
				 data: {action: 'XXXX', label: labeltxt, projectid: pid, type: window.esynOpts.type, data: JSON.stringify(window.stack)},
				 type: 'post',
				 dataType: 'text',
				 success: function(response){
					 console.log(response)
					 response = JSON.parse(response)
					 if(response['success'] == true){
						 alert('Saved successfully.')
					 } else {
					 	alert('Save failed. You may not have permission to edit this project, but you can save your work as your own copy ("Save > Save a copy"). \n Error message: ' + response['message'])
					 }
				 }
			});
	
		};
		//undo the stringify needed to save
		window.stack.metadata = JSON.parse(window.stack.metadata);
		update_style(); //visual style classes removed by check_changes

		//disable the box that lets you set the name
		$('#set_filename').prop('disabled',true);
	} else {
		alert("Nothing was saved")
	}
	
};

function load_from_server(){ //add history id
	//Ajax request

	//if we're loading a published project
	if(window.esynOpts.publishedid != '-1'){
		//load from published data
		var id = window.esynOpts.publishedid;
		console.log('trying to load a published graph id: ' + id);
		$.ajax({ url: '../manager.php',
	         data: {action: 'XXXX', publishedid: id},
	         type: 'get',
			 dataType: 'text',
	         success: function(uploaded_json) {
				console.log(uploaded_json);
	            //the file is a network stack
				//window.stack = JSON.parse(uploaded_json) //needed when coming from database
				var result = JSON.parse(uploaded_json);

				if(result.hasOwnProperty('success')){
					alert('The selected project could not be loaded. If you think this is an error please contact the administrators.')

					//continue a if we were starting a new project
					window.currentNwName = getDefaultNwName();
					document.getElementById('nw_name').value = window.currentNwName;
				} else {
					window.stack = result;
					//console.log(window.stack)
					window.stack.metadata = JSON.parse(window.stack.metadata)
					if(!window.stack.metadata.hasOwnProperty('nodecounter')){
						//check whether we have double escaped
						window.stack.metadata = JSON.parse(window.stack.metadata)
					}
					if(window.stack.metadata.hasOwnProperty('k') == false){
						window.stack.metadata['k'] = {} //for compatibility with projects saved before k added
					}
					if(!window.stack.metadata.hasOwnProperty('citations')){
						window.stack.metadata['citations'] = {}// init citations when loading networks created before we included them
					}
					
					//update the dropdown list of options
					var nwselect = $('#nwlist')
					var nwOpts = '<option selected="selected" value="">Select a network</option>'
					for(val in window.stack){
						if(val != 'metadata'){
							bit = '<option value ="' + val + '">' + val + '</option>'
							nwOpts += bit
						};
					};
						
					nwselect.html(nwOpts)
					
					//hack to get upload to bypass network selection dropdown the first time
					//will be removed when upload is separated from the viewer
					
					var nn = Object.keys(window.stack);
					nn.splice(nn.indexOf('metadata'),1)
					var autoselect = nn[0] 
					cy.load(JSON.parse(window.stack[autoselect])) //load and display the network
					cy.layout({name:'preset'})
					document.getElementById('nw_name').value = autoselect //set to name of current network
					window.currentNwName = autoselect //otherwise currentNwName will be undefined and will break updateStack()

					//apply the visual style classes
					update_style();
					
					//use something like this to also set the dropdown - need to guarantee it selects the right one, this doesn't fire an onchange
					//document.getElementById("nwlist").selectedIndex = 2;
					console.log('network loaded by autoselect')
				
					//printState();
				}
		
	         } //close success
		}); //close ajax

	} else {
		var id = window.esynOpts.projectid;
		var version = window.esynOpts.historyid;
		$.ajax({ url: '../manager.php',
	         data: {action: 'XXXX', projectid: id, historyid : version},
	         type: 'get',
			 dataType: 'text',
	         success: function(uploaded_json) {
				console.log(uploaded_json)
	            //the file is a network stack
				//window.stack = JSON.parse(uploaded_json) //needed when coming from database
				var result = JSON.parse(uploaded_json);
				if(result.hasOwnProperty('success')){
					alert('The selected project could not be loaded. If you think this is an error please contact the administrators.')

					//continue a if we were starting a new project
					window.currentNwName = getDefaultNwName();
					document.getElementById('nw_name').value = window.currentNwName;
				} else {
					window.stack = result;
					//console.log(window.stack)
					window.stack.metadata = JSON.parse(window.stack.metadata)
					if(!window.stack.metadata.hasOwnProperty('nodecounter')){
						//check whether we have double escaped
						window.stack.metadata = JSON.parse(window.stack.metadata)
					}
					if(window.stack.metadata.hasOwnProperty('k') == false){
						window.stack.metadata['k'] = {} //for compatibility with projects saved before k added
					}
					if(!window.stack.metadata.hasOwnProperty('citations')){
						window.stack.metadata['citations'] = {}// init citations when loading networks created before we included them
					}
					
					//update the dropdown list of options
					var nwselect = $('#nwlist')
					var nwOpts = '<option selected="selected" value="">Select a network</option>'
					for(val in window.stack){
						if(val != 'metadata'){
							bit = '<option value ="' + val + '">' + val + '</option>'
							nwOpts += bit
						};
					};
						
					nwselect.html(nwOpts)
					
					//hack to get upload to bypass network selection dropdown the first time
					//will be removed when upload is separated from the viewer
					
					var nn = Object.keys(window.stack);
					nn.splice(nn.indexOf('metadata'),1)
					var autoselect = nn[0] 
					cy.load(JSON.parse(window.stack[autoselect])) //load and display the network
					cy.layout({name:'preset'})
					document.getElementById('nw_name').value = autoselect //set to name of current network
					window.currentNwName = autoselect //otherwise currentNwName will be undefined and will break updateStack()
					
					//apply the visual style classes
					update_style();
					
					//use something like this to also set the dropdown - need to guarantee it selects the right one, this doesn't fire an onchange
					//document.getElementById("nwlist").selectedIndex = 2;
					console.log('network loaded by autoselect')
				
					//printState();
				}
	        }
		});
	}
	
};

//the old function
function load_stack(){
	//function to retrieve and set up a saved stack from the database via Ajax and PHP
	//will want to add parameters to set what is loaded
	//the test location is: http://www.eyeast.org/myeyeasttest/manager.php?action=view&groupid=1
	
	//Ajax request
	$.ajax({ url: 'http://www.eyeast.org/myeyeasttest/manager.php',
         data: {action: 'XXXX', groupid: '1'},
         type: 'get',
		 dataType: 'text',
         success: function(uploaded_json) {
            //the file is a network stack
			window.stack = JSON.parse(uploaded_json) //needed when coming from database
			console.log(uploaded_json)
			window.stack.metadata = JSON.parse(window.stack.metadata)
			
			//update the dropdown list of options
			var nwselect = $('#nwlist')
			var nwOpts = '<option selected="selected" value="">Select a network</option>'
			for(val in window.stack){
				if(val != 'metadata'){
					bit = '<option value ="' + val + '">' + val + '</option>'
					nwOpts += bit
				};
			};
				
			nwselect.html(nwOpts)
			
			//hack to get upload to bypass network selection dropdown the first time
			//will be removed when upload is separated from the viewer
			
			var nn = Object.keys(window.stack);
			nn.splice(nn.indexOf('metadata'),1)
			var autoselect = nn[0] 
			cy.load(JSON.parse(window.stack[autoselect])) //load and display the network
			cy.layout({name:'preset'})
			document.getElementById('nw_name').value = autoselect //set to name of current network
			
			//apply the visual style classes
			update_style();
			
			//use something like this to also set the dropdown - need to guarantee it selects the right one, this doesn't fire an onchange
			//document.getElementById("nwlist").selectedIndex = 2;
			console.log('network loaded by autoselect')
		
			//printState();
	
                  }
	});
	
	
};

function getDefaultNwName(){
	//generate the next available default name
	var nws = Object.keys(window.stack);
	var n = nws.length -1; //metadata shouldn't be included
	var name = 'Network' + n;
	var i = 0
	while(nws.indexOf(name) >= 0 && i < 100){
		n += 1;
		name = "Network" + n;
		i += 1;
	}
	if(i == 100){
		alert("a network name could not be generated, please contact an administrator")
		name = "__NAME__" //set to an unlikely name
	}
	return name;
}


///////////////////////////////////////////////////////////////////////////////////////
// code to add logic gates
///////////////////////////////////////////////////////////////////////////////////////

//function to create a node at the given position relative to the canvas window
//i.e. (0,0) is ALWAYS the top left, irrespective of pan/ zoom
function addNode(xPos, yPos, marking, type){ //should it be possible to specify a name?
	var default_name = "n" + window.stack.metadata['nodecounter']; //also the default id
	console.log(xPos.toString(), yPos.toString())
	cy.add([{
		group : "nodes",
		data : {
			id : default_name,
			name : default_name,
			marking : marking
			
		},
		classes: type,
		renderedPosition : {
			x : xPos, // x position,
			y : yPos // y position
		},
	}]);
	
	//update counter
	window.stack.metadata['nodecounter'] += 1;
	
	//update node/place name metadata
	window.stack.metadata[type][default_name] = marking;

	//return the id that was assigned so we can convert for modules
	return default_name;
}

//function to create an edge - src and tgt must be node IDs
//doesn't chec that the edge is value
function addEdge(src, tgt, multiplicity, type){
	var setID = window.stack.metadata.edgecounter.toString();
	cy.add([{
		group: "edges",
		data: {
			id: "e" + setID,
			source: src,
			target: tgt,
			multiplicity: multiplicity
		},
		classes: type
	}])  
	window.stack.metadata.edgecounter += 1;
}

//general function to add project data to the current network
function addProject(project){
	//nodes
	var convert = {} //map between node ids in the imported project and their assigned ids in the current project
	project.Network0.nodes.forEach(function(el){
		convert[el.data.id] = addNode(el.position.x, el.position.y, el.data.marking, el.classes)
	})
	//edges
	project.Network0.edges.forEach(function(el){
		addEdge(convert[el.data.source], convert[el.data.target], el.data.multiplicity, el.classes)
	})
}

//function to pre-process project data before dispatching addProject to put the data in the current network
//and is id 227 from my projects
function addIntoNetwork(id,published,cenx,ceny){ //cenx and ceny are the rendered coordinates of the click
	getProjectData(id,published,function(result){
		if(result.hasOwnProperty('metadata') == false){
			result = JSON.parse(result)
		}
		var nws = Object.keys(result)
		nws.splice(nws.indexOf('metadata'),1)
		if(nws.length > 1){
			console.log("ERROR: module with more than one network")
			alert("Error: this module contains more than one network, using the first")
		}
		result['Network0'] = result[nws[0]]
		if(result.Network0.hasOwnProperty('nodes') == false){
			result.Network0 = JSON.parse(result.Network0)	
		}
		//correct the node positions so the nodes are centered on the click location
		var z = cy.zoom()

		var cen = _.reduce(result.Network0.nodes, function(memo,val,key,list){
			memo.x = memo.x + val.position.x*z; 
			memo.y = memo.y + val.position.y*z;
			return memo},
			{x:0, y:0})
		
		cen.x = cen.x/result.Network0.nodes.length;
		cen.y = cen.y/result.Network0.nodes.length;
		console.log('centre of module: ',cen)

		diff = {x: cen.x - cenx, y: cen.y - ceny};

		console.log('difference: ',diff)

		_.each(result.Network0.nodes, function(el, idx, list){
			list[idx].position.x = list[idx].position.x*z - diff.x
			list[idx].position.y = list[idx].position.y*z - diff.y
		})

		addProject(result);
	})
}

//get editor state ready to insert module on click
function setupModule(id, published){
	window.nmode = true;
	window.ntype = 'structure';
	window.esynOpts.module = {id: id, published: published}
	console.log('module mode:',window.esynOpts.module);

	$('#moduleModal').modal('hide')

}

function resetStack(){
	//set up global variables
	window.nmode = true;
	window.sourcenode = -1; //will track the id of the source node in edge creation
	window.sourcetype = -1; //will track the type of the course node in edge creation
	window.ntype = "place" //type of node to be added
	window.etype = "normal" //type of edge to be added
	window.nodecounter = 0; //to keep track of the number of nodes that have been created

	//window.currentNwName is set up by cytoscape onload
	window.workingID = "none"; //the ID to use for the current stack when saving. If "none" then we are starting a new stack and will be given an ID to use

	//network data stack
	window.stack = {}
	window.stack['metadata'] = {} //to keep track of stack-level properties e.g. number of nodes
	window.stack.metadata['nodecounter'] = 0;
	window.stack.metadata['edgecounter'] = 0; //keep track of the number of edges that have been created - otherwise if you delete edges you can't add new ones any more
	//call the place and transition trackers the same as window.ntype so that can be used to access the correct data
	window.stack.metadata['place'] = {}; //object for all stack place names to keep track of adding disperse places
	window.stack.metadata['transition'] = {}; //object for all transition names to prevent duplicates - could use a list? this way they're accessed the same as pnames
	//trackers for place classes. Entries only exist of they are not default (normal place with no classes)
	window.stack.metadata['contains'] = {} //what places are a sub class of each coarse place
	window.stack.metadata['isa'] = {} //what coarse places each node is a sub class of
	//keep track of disperse places
	window.stack.metadata['disperse'] = {} //keyed by disperse place NAME -> network: [id(s)]
	//keep track of parameter k
	window.stack.metadata['k'] = {} //keyed by place NAME -> k (if a name is not a key for this network we know k is 1)
	//citations for elements
	window.stack.metadata['citations'] = {}; //keyed by element id -> [pmid]


	//if we have a project id, the user cannot change the label from the editor so hide the input box
	if(window.esynOpts.projectid != '-1' || window.esynOpts.publishedid != '-1'){
		$('#set_filename_form').remove();
	};

	//if we're working with a published project, disable to save button
	//even if somebody re-enabled it, the save function would create a new project id
	if(window.esynOpts.publishedid != '-1'){
		$('#save-online').prop('disabled',true);
	};
}
